<?php

declare(strict_types=1);

namespace App\Modules\Reports\Repositories;

use App\Core\Database;
use PDO;

class ReportRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function superAdminSummary(): array
    {
        return [
            'total_users' => $this->count('users', 'deleted_at IS NULL'),
            'total_recruiters' => $this->roleCount('recruiter'),
            'total_job_seekers' => $this->roleCount('job_seeker'),
            'total_hr_officers' => $this->roleCount('hr_officer'),
            'total_relationship_officers' => $this->roleCount('relationship_officer'),
            'total_jobs' => $this->count('jobs', 'deleted_at IS NULL'),
            'active_jobs' => $this->count('jobs', 'deleted_at IS NULL AND status IN ("published", "open", "assigned")'),
            'applications' => $this->count('job_applications'),
            'placements' => $this->count('job_applications', 'current_stage = "placed"'),
            'revenue' => $this->sum('payments', 'amount', 'status = "successful"'),
            'wallet_transaction_volume' => $this->sum('wallet_transactions', 'amount', 'status IN ("successful", "completed")'),
            'pending_verifications' => $this->pendingVerifications(),
            'recent_audit_logs' => $this->auditLogs([], 1, 10)['data'],
            'recent_activity_logs' => $this->activityLogs(10),
            'analytics' => [
                'users_by_role' => $this->chartFromPairs($this->userCountsByRole(), 'Users'),
                'jobs_by_status' => $this->chartFromPairs($this->countsBy('jobs', 'status', 'deleted_at IS NULL'), 'Jobs'),
                'applications_by_stage' => $this->chartFromPairs($this->countsBy('job_applications', 'current_stage'), 'Applications'),
                'revenue_by_month' => $this->chartFromPairs($this->monthlySums('payments', 'amount', 'status = "successful"'), 'Revenue'),
            ],
        ];
    }

    public function hrSummary(int $userId): array
    {
        $assignedCandidateWhere = 'assigned_hr_officer_id = :user_id OR referred_by_hr_officer_id = :user_id';
        $assignedJobsWhere = 'assigned_hr_officer_id = :user_id AND deleted_at IS NULL';

        return [
            'assigned_candidates' => $this->scalar(
                "SELECT COUNT(*) FROM job_seeker_profiles WHERE {$assignedCandidateWhere}",
                ['user_id' => $userId]
            ),
            'assigned_jobs' => $this->scalar("SELECT COUNT(*) FROM jobs WHERE {$assignedJobsWhere}", ['user_id' => $userId]),
            'pending_screenings' => $this->scalar(
                'SELECT COUNT(*) FROM job_applications
                 INNER JOIN jobs ON jobs.id = job_applications.job_id
                 WHERE jobs.assigned_hr_officer_id = :user_id AND job_applications.current_stage IN ("applied", "matched")',
                ['user_id' => $userId]
            ),
            'candidates_screened' => $this->scalar(
                'SELECT COUNT(DISTINCT job_applications.job_seeker_id)
                 FROM job_applications
                 INNER JOIN jobs ON jobs.id = job_applications.job_id
                 WHERE jobs.assigned_hr_officer_id = :user_id
                   AND job_applications.current_stage IN ("screening", "assessment_invited", "assessment_completed", "shortlisted", "interview_scheduled", "interview_completed", "offer_pending", "offer_accepted", "placed")',
                ['user_id' => $userId]
            ),
            'candidates_placed' => $this->scalar(
                'SELECT COUNT(*) FROM job_applications
                 INNER JOIN jobs ON jobs.id = job_applications.job_id
                 WHERE jobs.assigned_hr_officer_id = :user_id AND job_applications.current_stage = "placed"',
                ['user_id' => $userId]
            ),
            'pending_interviews' => $this->scalar(
                'SELECT COUNT(*) FROM interviews
                 INNER JOIN jobs ON jobs.id = interviews.job_id
                 WHERE jobs.assigned_hr_officer_id = :user_id AND interviews.status IN ("scheduled", "rescheduled")',
                ['user_id' => $userId]
            ),
            'interviews_today' => $this->scalar(
                'SELECT COUNT(*) FROM interviews
                 INNER JOIN jobs ON jobs.id = interviews.job_id
                 WHERE jobs.assigned_hr_officer_id = :user_id
                   AND interviews.status IN ("scheduled", "rescheduled")
                   AND DATE(interviews.scheduled_at) = CURRENT_DATE',
                ['user_id' => $userId]
            ),
            'assessment_results_pending' => $this->scalar(
                'SELECT COUNT(*) FROM assessment_assignments
                 LEFT JOIN assessment_results ON assessment_results.assignment_id = assessment_assignments.id
                 WHERE assessment_assignments.assigned_by = :user_id
                   AND assessment_assignments.status IN ("submitted", "manual_review")
                   AND assessment_results.id IS NULL',
                ['user_id' => $userId]
            ),
            'pipeline_summary' => $this->chartFromPairs(
                $this->pairs(
                    'SELECT job_applications.current_stage label, COUNT(*) value
                     FROM job_applications
                     INNER JOIN jobs ON jobs.id = job_applications.job_id
                     WHERE jobs.assigned_hr_officer_id = :user_id
                     GROUP BY job_applications.current_stage',
                    ['user_id' => $userId]
                ),
                'Candidates'
            ),
        ];
    }

    public function relationshipOfficerSummary(int $userId): array
    {
        return [
            'assigned_employers' => $this->scalar(
                'SELECT COUNT(DISTINCT recruiter_id) FROM jobs WHERE assigned_relationship_officer_id = :user_id AND deleted_at IS NULL',
                ['user_id' => $userId]
            ),
            'assigned_jobs' => $this->scalar(
                'SELECT COUNT(*) FROM jobs WHERE assigned_relationship_officer_id = :user_id AND deleted_at IS NULL',
                ['user_id' => $userId]
            ),
            'open_jobs' => $this->scalar(
                'SELECT COUNT(*) FROM jobs WHERE assigned_relationship_officer_id = :user_id AND deleted_at IS NULL AND status IN ("published", "open", "assigned")',
                ['user_id' => $userId]
            ),
            'jobs_pending_update' => $this->scalar(
                'SELECT COUNT(*) FROM jobs WHERE assigned_relationship_officer_id = :user_id AND deleted_at IS NULL AND status IN ("draft", "pending_approval", "paused")',
                ['user_id' => $userId]
            ),
            'fulfillment_progress' => $this->chartFromPairs(
                $this->pairs(
                    'SELECT status label, COUNT(*) value FROM jobs
                     WHERE assigned_relationship_officer_id = :user_id AND deleted_at IS NULL
                     GROUP BY status',
                    ['user_id' => $userId]
                ),
                'Jobs'
            ),
            'employer_activity' => $this->pairs(
                'SELECT recruiter_profiles.company_name label, COUNT(jobs.id) value
                 FROM jobs
                 INNER JOIN recruiter_profiles ON recruiter_profiles.id = jobs.recruiter_id
                 WHERE jobs.assigned_relationship_officer_id = :user_id AND jobs.deleted_at IS NULL
                 GROUP BY recruiter_profiles.company_name
                 ORDER BY value DESC
                 LIMIT 10',
                ['user_id' => $userId]
            ),
        ];
    }

    public function recruiterSummary(int $userId): array
    {
        $recruiterId = $this->scalar('SELECT id FROM recruiter_profiles WHERE user_id = :user_id LIMIT 1', ['user_id' => $userId]);

        return [
            'wallet_balance' => $this->numeric(
                'SELECT COALESCE(available_balance, 0) FROM wallets WHERE user_id = :user_id LIMIT 1',
                ['user_id' => $userId]
            ),
            'jobs_posted' => $this->scalar('SELECT COUNT(*) FROM jobs WHERE recruiter_id = :recruiter_id AND deleted_at IS NULL', ['recruiter_id' => $recruiterId]),
            'active_jobs' => $this->scalar(
                'SELECT COUNT(*) FROM jobs WHERE recruiter_id = :recruiter_id AND deleted_at IS NULL AND status IN ("published", "open", "assigned")',
                ['recruiter_id' => $recruiterId]
            ),
            'matched_candidates' => $this->scalar(
                'SELECT COUNT(*) FROM candidate_matches INNER JOIN jobs ON jobs.id = candidate_matches.job_id
                 WHERE jobs.recruiter_id = :recruiter_id',
                ['recruiter_id' => $recruiterId]
            ),
            'unlocked_candidates' => $this->scalar(
                'SELECT COUNT(*) FROM candidate_unlocks WHERE recruiter_id = :recruiter_id AND (expires_at IS NULL OR expires_at > NOW())',
                ['recruiter_id' => $recruiterId]
            ),
            'interviews_scheduled' => $this->scalar('SELECT COUNT(*) FROM interviews WHERE recruiter_id = :recruiter_id AND status IN ("scheduled", "rescheduled")', ['recruiter_id' => $recruiterId]),
            'hired_candidates' => $this->scalar(
                'SELECT COUNT(*) FROM job_applications INNER JOIN jobs ON jobs.id = job_applications.job_id
                 WHERE jobs.recruiter_id = :recruiter_id AND job_applications.current_stage = "placed"',
                ['recruiter_id' => $recruiterId]
            ),
            'payment_history' => $this->rows(
                'SELECT id, provider, provider_reference, internal_reference, amount, currency, status, purpose, verified_at, created_at
                 FROM payments WHERE user_id = :user_id ORDER BY created_at DESC, id DESC LIMIT 10',
                ['user_id' => $userId]
            ),
            'analytics' => [
                'jobs_by_status' => $this->chartFromPairs($this->pairs(
                    'SELECT status label, COUNT(*) value FROM jobs WHERE recruiter_id = :recruiter_id AND deleted_at IS NULL GROUP BY status',
                    ['recruiter_id' => $recruiterId]
                ), 'Jobs'),
                'applications_by_stage' => $this->chartFromPairs($this->pairs(
                    'SELECT job_applications.current_stage label, COUNT(*) value
                     FROM job_applications INNER JOIN jobs ON jobs.id = job_applications.job_id
                     WHERE jobs.recruiter_id = :recruiter_id GROUP BY job_applications.current_stage',
                    ['recruiter_id' => $recruiterId]
                ), 'Applications'),
            ],
        ];
    }

    public function jobSeekerSummary(int $userId): array
    {
        $profile = $this->row('SELECT * FROM job_seeker_profiles WHERE user_id = :user_id LIMIT 1', ['user_id' => $userId]);
        $profileId = (int) ($profile['id'] ?? 0);

        return [
            'profile_completion' => (int) ($profile['profile_completion_percentage'] ?? 0),
            'recommended_jobs' => $this->rows(
                'SELECT jobs.id, jobs.title, jobs.location, jobs.employment_type, jobs.work_mode, jobs.status, candidate_matches.match_score
                 FROM candidate_matches
                 INNER JOIN jobs ON jobs.id = candidate_matches.job_id
                 WHERE candidate_matches.job_seeker_id = :profile_id AND jobs.deleted_at IS NULL
                 ORDER BY candidate_matches.match_score DESC
                 LIMIT 10',
                ['profile_id' => $profileId]
            ),
            'applications' => $this->rows(
                'SELECT job_applications.id, job_applications.current_stage, job_applications.status, job_applications.match_score,
                        job_applications.submitted_at, jobs.title job_title
                 FROM job_applications
                 INNER JOIN jobs ON jobs.id = job_applications.job_id
                 WHERE job_applications.job_seeker_id = :profile_id
                 ORDER BY job_applications.created_at DESC
                 LIMIT 10',
                ['profile_id' => $profileId]
            ),
            'assessments' => $this->rows(
                'SELECT assessment_assignments.id, assessment_assignments.status, assessment_assignments.due_date,
                        assessments.title, assessment_results.percentage, assessment_results.status result_status
                 FROM assessment_assignments
                 INNER JOIN assessments ON assessments.id = assessment_assignments.assessment_id
                 LEFT JOIN assessment_results ON assessment_results.assignment_id = assessment_assignments.id
                 WHERE assessment_assignments.job_seeker_id = :profile_id
                 ORDER BY assessment_assignments.created_at DESC
                 LIMIT 10',
                ['profile_id' => $profileId]
            ),
            'interviews' => $this->rows(
                'SELECT id, job_id, interview_type, meeting_link, scheduled_at, duration_minutes, status
                 FROM interviews WHERE job_seeker_id = :profile_id ORDER BY scheduled_at DESC LIMIT 10',
                ['profile_id' => $profileId]
            ),
            'notifications' => $this->rows(
                'SELECT id, title, body, type, channel, read_at, created_at
                 FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC, id DESC LIMIT 10',
                ['user_id' => $userId]
            ),
            'messages' => [
                'unread_count' => $this->scalar(
                    'SELECT COUNT(*) FROM messages
                     INNER JOIN conversation_participants ON conversation_participants.conversation_id = messages.conversation_id
                     WHERE conversation_participants.user_id = :user_id
                       AND messages.sender_id != :user_id
                       AND messages.created_at > COALESCE(conversation_participants.last_read_at, "1970-01-01")',
                    ['user_id' => $userId]
                ),
            ],
            'analytics' => [
                'application_stages' => $this->chartFromPairs($this->pairs(
                    'SELECT current_stage label, COUNT(*) value FROM job_applications WHERE job_seeker_id = :profile_id GROUP BY current_stage',
                    ['profile_id' => $profileId]
                ), 'Applications'),
            ],
        ];
    }

    public function financialReport(): array
    {
        return [
            'revenue' => $this->sum('payments', 'amount', 'status = "successful"'),
            'pending_payments' => $this->sum('payments', 'amount', 'status = "pending"'),
            'wallet_credits' => $this->sum('wallet_transactions', 'amount', 'direction = "credit" AND status IN ("successful", "completed")'),
            'wallet_debits' => $this->sum('wallet_transactions', 'amount', 'direction = "debit" AND status IN ("successful", "completed")'),
            'transactions_by_type' => $this->chartFromPairs($this->countsBy('wallet_transactions', 'transaction_type'), 'Transactions'),
            'revenue_by_month' => $this->chartFromPairs($this->monthlySums('payments', 'amount', 'status = "successful"'), 'Revenue'),
        ];
    }

    public function placementReport(): array
    {
        return [
            'total_placements' => $this->count('job_applications', 'current_stage = "placed"'),
            'placements_by_month' => $this->chartFromPairs($this->monthlyCounts('job_applications', 'current_stage = "placed"'), 'Placements'),
            'placements_by_recruiter' => $this->chartFromPairs($this->pairs(
                'SELECT COALESCE(recruiter_profiles.company_name, CONCAT(users.first_name, " ", users.last_name)) label, COUNT(*) value
                 FROM job_applications
                 INNER JOIN jobs ON jobs.id = job_applications.job_id
                 INNER JOIN recruiter_profiles ON recruiter_profiles.id = jobs.recruiter_id
                 INNER JOIN users ON users.id = recruiter_profiles.user_id
                 WHERE job_applications.current_stage = "placed"
                 GROUP BY label
                 ORDER BY value DESC
                 LIMIT 10'
            ), 'Placements'),
            'placements_by_hr_officer' => $this->chartFromPairs($this->pairs(
                'SELECT CONCAT(users.first_name, " ", users.last_name) label, COUNT(*) value
                 FROM job_applications
                 INNER JOIN jobs ON jobs.id = job_applications.job_id
                 INNER JOIN users ON users.id = jobs.assigned_hr_officer_id
                 WHERE job_applications.current_stage = "placed"
                 GROUP BY label
                 ORDER BY value DESC
                 LIMIT 10'
            ), 'Placements'),
        ];
    }

    public function auditLogs(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), 100);
        $where = ['1 = 1'];
        $params = [];

        foreach (['actor_id', 'module', 'entity_type', 'action'] as $field) {
            if (($filters[$field] ?? '') !== '') {
                $where[] = "audit_logs.{$field} = :{$field}";
                $params[$field] = $filters[$field];
            }
        }

        $whereSql = implode(' AND ', $where);
        $total = $this->scalar("SELECT COUNT(*) FROM audit_logs WHERE {$whereSql}", $params);
        $offset = ($page - 1) * $perPage;
        $data = $this->rows(
            "SELECT audit_logs.*, CONCAT(users.first_name, ' ', users.last_name) actor_name, users.email actor_email
             FROM audit_logs
             LEFT JOIN users ON users.id = audit_logs.actor_id
             WHERE {$whereSql}
             ORDER BY audit_logs.created_at DESC, audit_logs.id DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return [
            'data' => $data,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) max(1, ceil($total / $perPage)),
            ],
        ];
    }

    public function exportRows(string $type): array
    {
        return match ($type) {
            'financial' => $this->rows(
                'SELECT internal_reference, provider_reference, amount, currency, status, purpose, verified_at, created_at
                 FROM payments ORDER BY created_at DESC, id DESC LIMIT 5000'
            ),
            'placements' => $this->rows(
                'SELECT job_applications.id, jobs.title job_title, recruiter_profiles.company_name recruiter,
                        job_applications.current_stage, job_applications.updated_at
                 FROM job_applications
                 INNER JOIN jobs ON jobs.id = job_applications.job_id
                 INNER JOIN recruiter_profiles ON recruiter_profiles.id = jobs.recruiter_id
                 WHERE job_applications.current_stage = "placed"
                 ORDER BY job_applications.updated_at DESC LIMIT 5000'
            ),
            'audit' => $this->rows(
                'SELECT actor_id, action, module, entity_type, entity_id, ip_address, created_at
                 FROM audit_logs ORDER BY created_at DESC, id DESC LIMIT 5000'
            ),
            'activity' => $this->rows(
                'SELECT user_id, activity_type, description, metadata_json, created_at
                 FROM activity_logs ORDER BY created_at DESC, id DESC LIMIT 5000'
            ),
            default => [],
        };
    }

    public function chartFromPairs(array $pairs, string $label): array
    {
        return [
            'labels' => array_map(static fn (array $pair): string => (string) $pair['label'], $pairs),
            'datasets' => [[
                'label' => $label,
                'data' => array_map(static fn (array $pair): float|int => is_numeric($pair['value']) ? (float) $pair['value'] : 0, $pairs),
            ]],
        ];
    }

    private function userCountsByRole(): array
    {
        return $this->pairs(
            'SELECT roles.name label, COUNT(DISTINCT users.id) value
             FROM roles
             LEFT JOIN user_roles ON user_roles.role_id = roles.id
             LEFT JOIN users ON users.id = user_roles.user_id AND users.deleted_at IS NULL
             GROUP BY roles.id, roles.name
             ORDER BY roles.id'
        );
    }

    private function pendingVerifications(): int
    {
        return $this->scalar(
            'SELECT
                (SELECT COUNT(*) FROM recruiter_profiles WHERE verification_status IN ("pending", "under_review")) +
                (SELECT COUNT(*) FROM recruiter_documents WHERE status = "pending") +
                (SELECT COUNT(*) FROM job_seeker_documents WHERE status = "pending")'
        );
    }

    private function activityLogs(int $limit): array
    {
        if (! $this->tableExists('activity_logs')) {
            return [];
        }

        return $this->rows(
            "SELECT activity_logs.*, CONCAT(users.first_name, ' ', users.last_name) user_name, users.email user_email
             FROM activity_logs
             LEFT JOIN users ON users.id = activity_logs.user_id
             ORDER BY activity_logs.created_at DESC, activity_logs.id DESC
             LIMIT {$limit}"
        );
    }

    private function tableExists(string $table): bool
    {
        $statement = $this->connection()->prepare(
            'SELECT COUNT(*)
             FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = :table'
        );
        $statement->execute(['table' => $table]);

        return (int) $statement->fetchColumn() > 0;
    }

    private function countsBy(string $table, string $column, string $where = '1 = 1'): array
    {
        return $this->pairs("SELECT {$column} label, COUNT(*) value FROM {$table} WHERE {$where} GROUP BY {$column}");
    }

    private function monthlyCounts(string $table, string $where = '1 = 1'): array
    {
        return $this->pairs(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') label, COUNT(*) value
             FROM {$table}
             WHERE {$where} AND created_at IS NOT NULL
             GROUP BY label
             ORDER BY label"
        );
    }

    private function monthlySums(string $table, string $column, string $where = '1 = 1'): array
    {
        return $this->pairs(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') label, COALESCE(SUM({$column}), 0) value
             FROM {$table}
             WHERE {$where} AND created_at IS NOT NULL
             GROUP BY label
             ORDER BY label"
        );
    }

    private function roleCount(string $role): int
    {
        return $this->scalar(
            'SELECT COUNT(DISTINCT users.id)
             FROM users
             INNER JOIN user_roles ON user_roles.user_id = users.id
             INNER JOIN roles ON roles.id = user_roles.role_id
             WHERE roles.slug = :role AND users.deleted_at IS NULL',
            ['role' => $role]
        );
    }

    private function count(string $table, string $where = '1 = 1'): int
    {
        return $this->scalar("SELECT COUNT(*) FROM {$table} WHERE {$where}");
    }

    private function sum(string $table, string $column, string $where = '1 = 1'): float
    {
        return $this->numeric("SELECT COALESCE(SUM({$column}), 0) FROM {$table} WHERE {$where}");
    }

    private function row(string $sql, array $params = []): ?array
    {
        $rows = $this->rows($sql, $params);

        return $rows[0] ?? null;
    }

    private function pairs(string $sql, array $params = []): array
    {
        return array_map(
            static fn (array $row): array => ['label' => $row['label'] ?? 'Unknown', 'value' => $row['value'] ?? 0],
            $this->rows($sql, $params)
        );
    }

    private function rows(string $sql, array $params = []): array
    {
        $statement = $this->connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    private function scalar(string $sql, array $params = []): int
    {
        $statement = $this->connection()->prepare($sql);
        $statement->execute($params);

        return (int) ($statement->fetchColumn() ?: 0);
    }

    private function numeric(string $sql, array $params = []): float
    {
        $statement = $this->connection()->prepare($sql);
        $statement->execute($params);

        return (float) ($statement->fetchColumn() ?: 0);
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
