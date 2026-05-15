<?php

declare(strict_types=1);

return [
    'CREATE TABLE IF NOT EXISTS conversations (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        conversation_type VARCHAR(80) NOT NULL,
        subject VARCHAR(180) NULL,
        job_id BIGINT UNSIGNED NULL,
        created_by BIGINT UNSIGNED NOT NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        CONSTRAINT fk_conversations_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE SET NULL,
        CONSTRAINT fk_conversations_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
        INDEX idx_conversations_type (conversation_type),
        INDEX idx_conversations_job (job_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    'CREATE TABLE IF NOT EXISTS conversation_participants (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        conversation_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        last_read_at TIMESTAMP NULL,
        created_at TIMESTAMP NULL,
        CONSTRAINT fk_conversation_participants_conversation FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
        CONSTRAINT fk_conversation_participants_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY uq_conversation_participants_user (conversation_id, user_id),
        INDEX idx_conversation_participants_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    'CREATE TABLE IF NOT EXISTS messages (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        conversation_id BIGINT UNSIGNED NOT NULL,
        sender_id BIGINT UNSIGNED NOT NULL,
        message_body TEXT NOT NULL,
        attachment_path VARCHAR(500) NULL,
        read_at TIMESTAMP NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        CONSTRAINT fk_messages_conversation FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
        CONSTRAINT fk_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE RESTRICT,
        INDEX idx_messages_conversation (conversation_id),
        INDEX idx_messages_sender (sender_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    'CREATE TABLE IF NOT EXISTS notifications (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL,
        title VARCHAR(180) NOT NULL,
        body TEXT NOT NULL,
        type VARCHAR(120) NOT NULL,
        channel VARCHAR(80) NOT NULL DEFAULT "in_app",
        data_json JSON NULL,
        read_at TIMESTAMP NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_notifications_user_read (user_id, read_at),
        INDEX idx_notifications_type (type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    'CREATE TABLE IF NOT EXISTS notification_preferences (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL UNIQUE,
        in_app_enabled TINYINT(1) NOT NULL DEFAULT 1,
        email_enabled TINYINT(1) NOT NULL DEFAULT 1,
        event_preferences_json JSON NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        CONSTRAINT fk_notification_preferences_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    'CREATE TABLE IF NOT EXISTS email_queue (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL,
        notification_id BIGINT UNSIGNED NULL,
        recipient_email VARCHAR(190) NOT NULL,
        subject VARCHAR(180) NOT NULL,
        body TEXT NOT NULL,
        status VARCHAR(80) NOT NULL DEFAULT "pending",
        attempts INT UNSIGNED NOT NULL DEFAULT 0,
        last_error TEXT NULL,
        available_at TIMESTAMP NULL,
        sent_at TIMESTAMP NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        CONSTRAINT fk_email_queue_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_email_queue_notification FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE SET NULL,
        INDEX idx_email_queue_status_available (status, available_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
];
