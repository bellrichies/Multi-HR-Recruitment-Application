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

    public function findByPhone(string $phone): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM users WHERE phone = :phone AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['phone' => $phone]);
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

    public function update(int $id, array $data): ?array
    {
        $statement = $this->connection()->prepare(
            'UPDATE users SET
                first_name = :first_name,
                last_name = :last_name,
                email = :email,
                phone = :phone,
                updated_at = NOW()
             WHERE id = :id AND deleted_at IS NULL'
        );
        $statement->execute([
            'id' => $id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'] ?? null,
        ]);

        if (! empty($data['password_hash'])) {
            $password = $this->connection()->prepare('UPDATE users SET password_hash = :password_hash, updated_at = NOW() WHERE id = :id');
            $password->execute(['id' => $id, 'password_hash' => $data['password_hash']]);
        }

        return $this->findById($id);
    }

    public function updateStatus(int $id, string $status): ?array
    {
        $statement = $this->connection()->prepare(
            'UPDATE users SET status = :status, updated_at = NOW() WHERE id = :id AND deleted_at IS NULL'
        );
        $statement->execute(['id' => $id, 'status' => $status]);

        return $this->findById($id);
    }

    public function list(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), 100);
        $where = ['users.deleted_at IS NULL'];
        $params = [];

        if (($filters['status'] ?? '') !== '') {
            $where[] = 'users.status = :status';
            $params['status'] = $filters['status'];
        }

        if (($filters['role'] ?? '') !== '') {
            $where[] = 'EXISTS (
                SELECT 1 FROM user_roles role_filter
                INNER JOIN roles filter_roles ON filter_roles.id = role_filter.role_id
                WHERE role_filter.user_id = users.id AND filter_roles.slug = :role
            )';
            $params['role'] = $filters['role'];
        }

        if (($filters['search'] ?? '') !== '') {
            $where[] = '(users.first_name LIKE :search OR users.last_name LIKE :search OR users.email LIKE :search OR users.phone LIKE :search)';
            $params['search'] = '%' . trim((string) $filters['search']) . '%';
        }

        $sortMap = [
            'name' => 'users.first_name',
            'email' => 'users.email',
            'status' => 'users.status',
            'created_at' => 'users.created_at',
            'last_login_at' => 'users.last_login_at',
        ];
        $sort = $sortMap[(string) ($filters['sort'] ?? 'created_at')] ?? 'users.created_at';
        $direction = strtolower((string) ($filters['direction'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';
        $secondarySort = $sort === 'users.first_name' ? ', users.last_name ASC' : ', users.id DESC';
        $whereSql = implode(' AND ', $where);

        $count = $this->connection()->prepare("SELECT COUNT(*) FROM users WHERE {$whereSql}");
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $offset = max(0, ($page - 1) * $perPage);

        $statement = $this->connection()->prepare(
            "SELECT users.*
             FROM users
             WHERE {$whereSql}
             ORDER BY {$sort} {$direction}{$secondarySort}
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $statement->execute($params);
        $users = $statement->fetchAll();

        return [
            'data' => $users,
            'roles' => $this->rolesForUsers(array_map(fn (array $user): int => (int) $user['id'], $users)),
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) max(1, ceil($total / $perPage)),
            ],
        ];
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

    public function rolesForUsers(array $userIds): array
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));

        if ($userIds === []) {
            return [];
        }

        $placeholders = [];
        $params = [];

        foreach ($userIds as $index => $userId) {
            $key = 'user_id_' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $userId;
        }

        $statement = $this->connection()->prepare(
            'SELECT user_roles.user_id, roles.*
             FROM user_roles
             INNER JOIN roles ON roles.id = user_roles.role_id
             WHERE user_roles.user_id IN (' . implode(', ', $placeholders) . ')
             ORDER BY roles.name'
        );
        $statement->execute($params);
        $roles = [];

        foreach ($statement->fetchAll() as $role) {
            $roles[(int) $role['user_id']][] = $role;
        }

        return $roles;
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

    public function activeUsersWithRole(string $roleSlug): int
    {
        $statement = $this->connection()->prepare(
            'SELECT COUNT(DISTINCT users.id)
             FROM users
             INNER JOIN user_roles ON user_roles.user_id = users.id
             INNER JOIN roles ON roles.id = user_roles.role_id
             WHERE roles.slug = :role AND users.status = "active" AND users.deleted_at IS NULL'
        );
        $statement->execute(['role' => $roleSlug]);

        return (int) $statement->fetchColumn();
    }

    public function messageParticipants(int $excludeUserId, string $search = '', int $limit = 20): array
    {
        $limit = min(max(1, $limit), 100);
        $where = [
            'users.deleted_at IS NULL',
            'users.status = "active"',
            'users.id <> :exclude_user_id',
        ];
        $params = ['exclude_user_id' => $excludeUserId];

        $search = trim($search);

        if ($search !== '') {
            $where[] = '(users.first_name LIKE :search OR users.last_name LIKE :search OR users.email LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $whereSql = implode(' AND ', $where);
        $statement = $this->connection()->prepare(
            "SELECT users.id, users.first_name, users.last_name, users.email,
                GROUP_CONCAT(roles.slug ORDER BY roles.slug SEPARATOR ',') role_slugs
             FROM users
             LEFT JOIN user_roles ON user_roles.user_id = users.id
             LEFT JOIN roles ON roles.id = user_roles.role_id
             WHERE {$whereSql}
             GROUP BY users.id, users.first_name, users.last_name, users.email
             ORDER BY users.first_name ASC, users.last_name ASC, users.email ASC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function markLastLogin(int $userId): void
    {
        $statement = $this->connection()->prepare(
            'UPDATE users SET last_login_at = NOW(), updated_at = NOW() WHERE id = :id'
        );
        $statement->execute(['id' => $userId]);
    }
}
