<?php

declare(strict_types=1);

return [
    'CREATE TABLE IF NOT EXISTS activity_logs (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NULL,
        activity_type VARCHAR(120) NOT NULL,
        description TEXT NOT NULL,
        metadata_json JSON NULL,
        created_at TIMESTAMP NULL,
        CONSTRAINT fk_activity_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_activity_logs_user (user_id),
        INDEX idx_activity_logs_type (activity_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
];
