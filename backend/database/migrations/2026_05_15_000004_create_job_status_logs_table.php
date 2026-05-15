<?php

declare(strict_types=1);

return [
    'CREATE TABLE IF NOT EXISTS job_status_logs (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        job_id BIGINT UNSIGNED NOT NULL,
        from_status VARCHAR(80) NULL,
        to_status VARCHAR(80) NOT NULL,
        changed_by BIGINT UNSIGNED NOT NULL,
        action VARCHAR(150) NOT NULL,
        note TEXT NULL,
        created_at TIMESTAMP NULL,
        CONSTRAINT fk_job_status_logs_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
        CONSTRAINT fk_job_status_logs_changed_by FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE RESTRICT,
        INDEX idx_job_status_logs_job_id (job_id),
        INDEX idx_job_status_logs_changed_by (changed_by)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
];
