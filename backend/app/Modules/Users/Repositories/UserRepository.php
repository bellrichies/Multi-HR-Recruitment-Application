<?php

declare(strict_types=1);

namespace App\Modules\Users\Repositories;

use App\Core\Database;
use PDO;

class UserRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM users WHERE email = :email AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['email' => strtolower($email)]);
        $user = $statement->fetch();

        return is_array($user) ? $user : null;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM users WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return is_array($user) ? $user : null;
    }

    public function create(array $data): array
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO users (uuid, first_name, last_name, email, phone, password_hash, status, created_at, updated_at)
             VALUES (:uuid, :first_name, :last_name, :email, :phone, :password_hash, :status, NOW(), NOW())'
        );
        $statement->execute([
            'uuid' => $data['uuid'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'] ?? null,
            'password_hash' => $data['password_hash'],
            'status' => $data['status'] ?? 'pending',
        ]);

        return $this->findById((int) $this->connection()->lastInsertId());
    }

    public function roles(int $userId): array
    {
        $statement = $this->connection()->prepare(
            'SELECT roles.* FROM roles
             INNER JOIN user_roles ON user_roles.role_id = roles.id
             WHERE user_roles.user_id = :user_id'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function permissions(int $userId): array
    {
        $statement = $this->connection()->prepare(
            'SELECT DISTINCT permissions.slug FROM permissions
             INNER JOIN role_permissions ON role_permissions.permission_id = permissions.id
             INNER JOIN user_roles ON user_roles.role_id = role_permissions.role_id
             WHERE user_roles.user_id = :user_id'
        );
        $statement->execute(['user_id' => $userId]);

        return array_column($statement->fetchAll(), 'slug');
    }

    public function assignRoles(int $userId, array $roleIds): void
    {
        $db = $this->connection();
        $db->prepare('DELETE FROM user_roles WHERE user_id = :user_id')->execute(['user_id' => $userId]);
        $statement = $db->prepare('INSERT INTO user_roles (user_id, role_id, created_at) VALUES (:user_id, :role_id, NOW())');

        foreach ($roleIds as $roleId) {
            $statement->execute(['user_id' => $userId, 'role_id' => (int) $roleId]);
        }
    }

    public function markLastLogin(int $userId): void
    {
        $statement = $this->connection()->prepare(
            'UPDATE users SET last_login_at = NOW(), updated_at = NOW() WHERE id = :id'
        );
        $statement->execute(['id' => $userId]);
    }
}
