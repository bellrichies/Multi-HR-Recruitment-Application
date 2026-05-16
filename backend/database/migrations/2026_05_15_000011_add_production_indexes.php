<?php

declare(strict_types=1);

return [
    'ALTER TABLE users ADD INDEX idx_users_status_created_at (status, created_at)',
    'ALTER TABLE jobs ADD INDEX idx_jobs_status_created_at (status, created_at)',
    'ALTER TABLE jobs ADD INDEX idx_jobs_recruiter_status (recruiter_id, status)',
    'ALTER TABLE job_applications ADD INDEX idx_job_applications_stage_created_at (current_stage, created_at)',
    'ALTER TABLE job_applications ADD INDEX idx_job_applications_job_stage (job_id, current_stage)',
    'ALTER TABLE candidate_matches ADD INDEX idx_candidate_matches_score (job_id, match_score)',
    'ALTER TABLE candidate_unlocks ADD INDEX idx_candidate_unlocks_expires (recruiter_id, job_seeker_id, expires_at)',
    'ALTER TABLE payments ADD INDEX idx_payments_status_created_at (status, created_at)',
    'ALTER TABLE wallet_transactions ADD INDEX idx_wallet_transactions_status_created_at (status, created_at)',
    'ALTER TABLE audit_logs ADD INDEX idx_audit_logs_created_at (created_at)',
    'ALTER TABLE notifications ADD INDEX idx_notifications_created_at (user_id, created_at)',
];
