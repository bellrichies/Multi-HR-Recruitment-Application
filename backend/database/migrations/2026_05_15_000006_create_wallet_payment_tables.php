<?php

declare(strict_types=1);

return [
    'CREATE TABLE IF NOT EXISTS wallets (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL UNIQUE,
        wallet_type VARCHAR(80) NOT NULL DEFAULT "user",
        currency CHAR(3) NOT NULL DEFAULT "NGN",
        available_balance DECIMAL(14,2) NOT NULL DEFAULT 0,
        ledger_balance DECIMAL(14,2) NOT NULL DEFAULT 0,
        status VARCHAR(80) NOT NULL DEFAULT "active",
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        CONSTRAINT fk_wallets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    'CREATE TABLE IF NOT EXISTS wallet_transactions (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        wallet_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        reference VARCHAR(120) NOT NULL UNIQUE,
        transaction_type VARCHAR(80) NOT NULL,
        direction ENUM("credit", "debit") NOT NULL,
        amount DECIMAL(14,2) NOT NULL,
        balance_before DECIMAL(14,2) NOT NULL,
        balance_after DECIMAL(14,2) NOT NULL,
        status VARCHAR(80) NOT NULL,
        description VARCHAR(255) NULL,
        metadata_json JSON NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        CONSTRAINT fk_wallet_transactions_wallet FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE RESTRICT,
        CONSTRAINT fk_wallet_transactions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
        INDEX idx_wallet_transactions_wallet (wallet_id),
        INDEX idx_wallet_transactions_reference (reference)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    'CREATE TABLE IF NOT EXISTS payments (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL,
        wallet_id BIGINT UNSIGNED NULL,
        provider VARCHAR(80) NOT NULL,
        provider_reference VARCHAR(120) NULL UNIQUE,
        internal_reference VARCHAR(120) NOT NULL UNIQUE,
        amount DECIMAL(14,2) NOT NULL,
        currency CHAR(3) NOT NULL DEFAULT "NGN",
        status VARCHAR(80) NOT NULL DEFAULT "pending",
        purpose VARCHAR(80) NOT NULL,
        metadata_json JSON NULL,
        verified_at TIMESTAMP NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        CONSTRAINT fk_payments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
        CONSTRAINT fk_payments_wallet FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE SET NULL,
        INDEX idx_payments_provider_reference (provider_reference),
        INDEX idx_payments_internal_reference (internal_reference)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    'CREATE TABLE IF NOT EXISTS payment_webhook_events (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        provider VARCHAR(80) NOT NULL,
        event_type VARCHAR(120) NOT NULL,
        event_reference VARCHAR(160) NOT NULL,
        payload_json JSON NOT NULL,
        processed_at TIMESTAMP NULL,
        created_at TIMESTAMP NULL,
        UNIQUE KEY uq_payment_webhook_events_provider_event_reference (provider, event_type, event_reference)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    'INSERT IGNORE INTO wallets (user_id, wallet_type, currency, status, created_at, updated_at)
     SELECT users.id, "user", "NGN", "active", NOW(), NOW()
     FROM users
     WHERE users.id NOT IN (
        SELECT user_roles.user_id
        FROM user_roles
        INNER JOIN roles ON roles.id = user_roles.role_id
        WHERE roles.slug = "super_admin"
     )',
];
