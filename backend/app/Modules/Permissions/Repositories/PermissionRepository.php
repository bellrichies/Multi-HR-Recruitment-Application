<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Repositories;

use App\Core\Database;
use PDO;

class PermissionRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function all(): array
    {
        return $this->connection()->query('SELECT * FROM permissions ORDER BY module, name')->fetchAll();
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM permissions WHERE slug = :slug LIMIT 1');
        $statement->execute(['slug' => $slug]);
        $permission = $statement->fetch();

        return is_array($permission) ? $permission : null;
    }

    public function idsForSlugs(array $slugs): array
    {
        if ($slugs === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($slugs), '?'));
        $statement = $this->connection()->prepare("SELECT id FROM permissions WHERE slug IN ({$placeholders})");
        $statement->execute(array_values($slugs));

        return array_map('intval', array_column($statement->fetchAll(), 'id'));
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
