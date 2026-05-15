<?php

declare(strict_types=1);

return [
    'CREATE TABLE IF NOT EXISTS migrations (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        migration VARCHAR(255) NOT NULL UNIQUE,
        executed_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    'CREATE TABLE IF NOT EXISTS users (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        uuid CHAR(36) NOT NULL UNIQUE,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        phone VARCHAR(30) NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        status ENUM("pending", "active", "suspended", "deactivated", "rejected") NOT NULL DEFAULT "pending",
        email_verified_at TIMESTAMP NULL,
        phone_verified_at TIMESTAMP NULL,
        last_login_at TIMESTAMP NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        deleted_at TIMESTAMP NULL,
        INDEX idx_users_email (email),
        INDEX idx_users_phone (phone),
        INDEX idx_users_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    'CREATE TABLE IF NOT EXISTS roles (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL UNIQUE,
        description VARCHAR(255) NULL,
        is_system TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        INDEX idx_roles_slug (slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    'CREATE TABLE IF NOT EXISTS permissions (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(150) NOT NULL,
        slug VARCHAR(150) NOT NULL UNIQUE,
        module VARCHAR(80) NOT NULL,
        description VARCHAR(255) NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        INDEX idx_permissions_slug (slug),
        INDEX idx_permissions_module (module)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    'CREATE TABLE IF NOT EXISTS user_roles (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL,
        role_id BIGINT UNSIGNED NOT NULL,
        created_at TIMESTAMP NULL,
        UNIQUE KEY uq_user_roles_user_role (user_id, role_id),
        CONSTRAINT fk_user_roles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_user_roles_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    'CREATE TABLE IF NOT EXISTS role_permissions (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        role_id BIGINT UNSIGNED NOT NULL,
        permission_id BIGINT UNSIGNED NOT NULL,
        created_at TIMESTAMP NULL,
        UNIQUE KEY uq_role_permissions_role_permission (role_id, permission_id),
        CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
        CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    'CREATE TABLE IF NOT EXISTS auth_token_blacklist (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        jti VARCHAR(64) NOT NULL UNIQUE,
        user_id BIGINT UNSIGNED NOT NULL,
        expires_at TIMESTAMP NOT NULL,
        created_at TIMESTAMP NULL,
        INDEX idx_auth_token_blacklist_jti (jti),
        CONSTRAINT fk_auth_token_blacklist_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    'CREATE TABLE IF NOT EXISTS audit_logs (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        actor_id BIGINT UNSIGNED NULL,
        action VARCHAR(150) NOT NULL,
        module VARCHAR(80) NOT NULL,
        entity_type VARCHAR(150) NULL,
        entity_id BIGINT UNSIGNED NULL,
        old_values_json JSON NULL,
        new_values_json JSON NULL,
        ip_address VARCHAR(45) NULL,
        user_agent VARCHAR(255) NULL,
        created_at TIMESTAMP NULL,
        INDEX idx_audit_logs_actor_id (actor_id),
        INDEX idx_audit_logs_module (module),
        INDEX idx_audit_logs_entity (entity_type, entity_id),
        CONSTRAINT fk_audit_logs_actor FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
];
