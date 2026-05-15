<?php

declare(strict_types=1);

return [
    'CREATE TABLE IF NOT EXISTS job_applications (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        job_id BIGINT UNSIGNED NOT NULL,
        job_seeker_id BIGINT UNSIGNED NOT NULL,
        applied_by BIGINT UNSIGNED NOT NULL,
        status VARCHAR(80) NOT NULL DEFAULT "active",
        current_stage VARCHAR(80) NOT NULL DEFAULT "applied",
        cover_letter TEXT NULL,
        match_score DECIMAL(5,2) NULL,
        submitted_at TIMESTAMP NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        UNIQUE KEY uq_job_applications_job_candidate (job_id, job_seeker_id),
        CONSTRAINT fk_job_applications_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
        CONSTRAINT fk_job_applications_profile FOREIGN KEY (job_seeker_id) REFERENCES job_seeker_profiles(id) ON DELETE CASCADE,
        CONSTRAINT fk_job_applications_applied_by FOREIGN KEY (applied_by) REFERENCES users(id) ON DELETE RESTRICT,
        INDEX idx_job_applications_job_id (job_id),
        INDEX idx_job_applications_job_seeker_id (job_seeker_id),
        INDEX idx_job_applications_stage (current_stage)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    'CREATE TABLE IF NOT EXISTS application_stage_logs (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        application_id BIGINT UNSIGNED NOT NULL,
        from_stage VARCHAR(80) NULL,
        to_stage VARCHAR(80) NOT NULL,
        changed_by BIGINT UNSIGNED NOT NULL,
        note TEXT NULL,
        created_at TIMESTAMP NULL,
        CONSTRAINT fk_application_stage_logs_application FOREIGN KEY (application_id) REFERENCES job_applications(id) ON DELETE CASCADE,
        CONSTRAINT fk_application_stage_logs_changed_by FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE RESTRICT,
        INDEX idx_application_stage_logs_application (application_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    'CREATE TABLE IF NOT EXISTS candidate_matches (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        job_id BIGINT UNSIGNED NOT NULL,
        job_seeker_id BIGINT UNSIGNED NOT NULL,
        matched_by BIGINT UNSIGNED NULL,
        match_score DECIMAL(5,2) NOT NULL DEFAULT 0,
        match_reason TEXT NULL,
        status VARCHAR(80) NOT NULL DEFAULT "recommended",
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        UNIQUE KEY uq_candidate_matches_job_candidate (job_id, job_seeker_id),
        CONSTRAINT fk_candidate_matches_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
        CONSTRAINT fk_candidate_matches_profile FOREIGN KEY (job_seeker_id) REFERENCES job_seeker_profiles(id) ON DELETE CASCADE,
        CONSTRAINT fk_candidate_matches_matched_by FOREIGN KEY (matched_by) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_candidate_matches_job_id (job_id),
        INDEX idx_candidate_matches_profile (job_seeker_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    'CREATE TABLE IF NOT EXISTS candidate_unlocks (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        recruiter_id BIGINT UNSIGNED NOT NULL,
        job_seeker_id BIGINT UNSIGNED NOT NULL,
        job_id BIGINT UNSIGNED NULL,
        transaction_id BIGINT UNSIGNED NULL,
        unlocked_by BIGINT UNSIGNED NOT NULL,
        expires_at TIMESTAMP NULL,
        created_at TIMESTAMP NULL,
        CONSTRAINT fk_candidate_unlocks_recruiter FOREIGN KEY (recruiter_id) REFERENCES recruiter_profiles(id) ON DELETE CASCADE,
        CONSTRAINT fk_candidate_unlocks_profile FOREIGN KEY (job_seeker_id) REFERENCES job_seeker_profiles(id) ON DELETE CASCADE,
        CONSTRAINT fk_candidate_unlocks_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
        CONSTRAINT fk_candidate_unlocks_user FOREIGN KEY (unlocked_by) REFERENCES users(id) ON DELETE RESTRICT,
        INDEX idx_candidate_unlocks_recruiter (recruiter_id),
        INDEX idx_candidate_unlocks_profile (job_seeker_id),
        INDEX idx_candidate_unlocks_job (job_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
];
