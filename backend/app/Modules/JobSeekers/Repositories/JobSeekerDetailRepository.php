<?php

declare(strict_types=1);

namespace App\Modules\JobSeekers\Repositories;

use App\Core\Database;
use PDO;

class JobSeekerDetailRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function createSkill(int $profileId, array $data): array
    {
        return $this->insertAndFetch('job_seeker_skills', [
            'job_seeker_id' => $profileId,
            'skill_name' => $data['skill_name'],
            'proficiency_level' => $data['proficiency_level'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function createWorkExperience(int $profileId, array $data): array
    {
        return $this->insertAndFetch('job_seeker_work_experiences', [
            'job_seeker_id' => $profileId,
            'company_name' => $data['company_name'],
            'job_title' => $data['job_title'],
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'is_current' => (int) ($data['is_current'] ?? false),
            'description' => $data['description'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function createEducation(int $profileId, array $data): array
    {
        return $this->insertAndFetch('job_seeker_educations', [
            'job_seeker_id' => $profileId,
            'institution' => $data['institution'],
            'qualification' => $data['qualification'],
            'field_of_study' => $data['field_of_study'] ?? null,
            'start_year' => $data['start_year'] ?? null,
            'end_year' => $data['end_year'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function createCertification(int $profileId, array $data): array
    {
        return $this->insertAndFetch('job_seeker_certifications', [
            'job_seeker_id' => $profileId,
            'name' => $data['name'],
            'issuer' => $data['issuer'] ?? null,
            'issue_date' => $data['issue_date'] ?? null,
            'expiry_date' => $data['expiry_date'] ?? null,
            'file_path' => $data['file_path'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function createDocument(int $profileId, string $documentType, string $filePath): array
    {
        return $this->insertAndFetch('job_seeker_documents', [
            'job_seeker_id' => $profileId,
            'document_type' => $documentType,
            'file_path' => $filePath,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function createGuarantor(int $profileId, array $data): array
    {
        return $this->insertAndFetch('guarantors', [
            'job_seeker_id' => $profileId,
            'full_name' => $data['full_name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'relationship' => $data['relationship'] ?? null,
            'address' => $data['address'] ?? null,
            'occupation' => $data['occupation'] ?? null,
            'document_path' => $data['document_path'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function allForProfile(int $profileId): array
    {
        return [
            'skills' => $this->whereProfile('job_seeker_skills', $profileId),
            'work_experiences' => $this->whereProfile('job_seeker_work_experiences', $profileId),
            'educations' => $this->whereProfile('job_seeker_educations', $profileId),
            'certifications' => $this->whereProfile('job_seeker_certifications', $profileId),
            'documents' => $this->whereProfile('job_seeker_documents', $profileId),
            'guarantors' => $this->whereProfile('guarantors', $profileId),
        ];
    }

    public function reviewDocument(int $documentId, string $status, int $reviewerId): array
    {
        $statement = $this->connection()->prepare(
            'UPDATE job_seeker_documents
             SET status = :status, reviewed_by = :reviewed_by, reviewed_at = NOW(), updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $documentId,
            'status' => $status,
            'reviewed_by' => $reviewerId,
        ]);

        $fetch = $this->connection()->prepare('SELECT * FROM job_seeker_documents WHERE id = :id LIMIT 1');
        $fetch->execute(['id' => $documentId]);
        $document = $fetch->fetch();

        if (! is_array($document)) {
            throw new \RuntimeException('Document review failed.');
        }

        return $document;
    }

    private function whereProfile(string $table, int $profileId): array
    {
        $statement = $this->connection()->prepare("SELECT * FROM {$table} WHERE job_seeker_id = :id ORDER BY id DESC");
        $statement->execute(['id' => $profileId]);

        return $statement->fetchAll();
    }

    private function insertAndFetch(string $table, array $data): array
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn (string $column): string => ':' . $column, $columns);
        $statement = $this->connection()->prepare(
            'INSERT INTO ' . $table . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')'
        );
        $statement->execute($data);
        $id = (int) $this->connection()->lastInsertId();
        $fetch = $this->connection()->prepare("SELECT * FROM {$table} WHERE id = :id LIMIT 1");
        $fetch->execute(['id' => $id]);

        return $fetch->fetch();
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
