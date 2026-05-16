<?php

declare(strict_types=1);

return [
    'ALTER TABLE conversation_participants ADD COLUMN is_favorite TINYINT(1) NOT NULL DEFAULT 0 AFTER last_read_at',
    'ALTER TABLE conversation_participants ADD INDEX idx_conversation_participants_user_favorite (user_id, is_favorite)',
];
