<?php

declare(strict_types=1);

namespace App\Modules\Audit\Repositories;

use App\Core\Database;
use PDO;

class AuditLogRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function create(array $data): void
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO audit_logs
             (actor_id, action, module, entity_type, entity_id, old_values_json, new_values_json, ip_address, user_agent, created_at)
             VALUES
             (:actor_id, :action, :module, :entity_type, :entity_id, :old_values_json, :new_values_json, :ip_address, :user_agent, NOW())'
        );
        $statement->execute([
            'actor_id' => $data['actor_id'] ?? null,
            'action' => $data['action'],
            'module' => $data['module'],
            'entity_type' => $data['entity_type'] ?? null,
            'entity_id' => $data['entity_id'] ?? null,
            'old_values_json' => isset($data['old_values'])
                ? json_encode($data['old_values'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES)
                : null,
            'new_values_json' => isset($data['new_values'])
                ? json_encode($data['new_values'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES)
                : null,
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
        ]);
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
