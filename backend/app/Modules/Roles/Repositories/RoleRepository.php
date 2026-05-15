<?php

declare(strict_types=1);

namespace App\Modules\Roles\Repositories;

use App\Core\Database;
use PDO;

class RoleRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function all(): array
    {
        return $this->connection()->query('SELECT * FROM roles ORDER BY name')->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM roles WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $role = $statement->fetch();

        return is_array($role) ? $role : null;
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM roles WHERE slug = :slug LIMIT 1');
        $statement->execute(['slug' => $slug]);
        $role = $statement->fetch();

        return is_array($role) ? $role : null;
    }

    public function create(array $data): array
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO roles (name, slug, description, is_system, created_at, updated_at)
             VALUES (:name, :slug, :description, :is_system, NOW(), NOW())'
        );
        $statement->execute([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'is_system' => (int) ($data['is_system'] ?? false),
        ]);

        return $this->findById((int) $this->connection()->lastInsertId());
    }

    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $db = $this->connection();
        $db->prepare('DELETE FROM role_permissions WHERE role_id = :role_id')->execute(['role_id' => $roleId]);
        $statement = $db->prepare(
            'INSERT INTO role_permissions (role_id, permission_id, created_at) VALUES (:role_id, :permission_id, NOW())'
        );

        foreach ($permissionIds as $permissionId) {
            $statement->execute(['role_id' => $roleId, 'permission_id' => (int) $permissionId]);
        }
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
