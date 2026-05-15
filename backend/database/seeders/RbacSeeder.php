<?php

declare(strict_types=1);

use App\Support\Auth\Password;

return function (PDO $db): void {
    $roles = [
        ['Super Admin', 'super_admin', 'Full platform access.'],
        ['Relationship Officer', 'relationship_officer', 'Employer-facing operations user.'],
        ['HR Officer', 'hr_officer', 'Candidate lifecycle manager.'],
        ['Recruiter', 'recruiter', 'Employer or hiring organization.'],
        ['Job Seeker', 'job_seeker', 'Candidate seeking work opportunities.'],
    ];

    $roleStatement = $db->prepare(
        'INSERT INTO roles (name, slug, description, is_system, created_at, updated_at)
         VALUES (:name, :slug, :description, 1, NOW(), NOW())
         ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), updated_at = NOW()'
    );

    foreach ($roles as [$name, $slug, $description]) {
        $roleStatement->execute(compact('name', 'slug', 'description'));
    }

    $permissions = config('permissions.permissions', []);
    $permissionStatement = $db->prepare(
        'INSERT INTO permissions (name, slug, module, description, created_at, updated_at)
         VALUES (:name, :slug, :module, :description, NOW(), NOW())
         ON DUPLICATE KEY UPDATE name = VALUES(name), module = VALUES(module), description = VALUES(description), updated_at = NOW()'
    );

    foreach ($permissions as $permission) {
        [$module] = explode('.', $permission, 2);
        $name = ucwords(str_replace(['.', '_'], [' ', ' '], $permission));
        $description = "Allows {$permission}.";
        $permissionStatement->execute([
            'name' => $name,
            'slug' => $permission,
            'module' => $module,
            'description' => $description,
        ]);
    }

    $superAdminRoleId = (int) $db->query('SELECT id FROM roles WHERE slug = "super_admin"')->fetchColumn();
    $permissionIds = $db->query('SELECT id FROM permissions')->fetchAll(PDO::FETCH_COLUMN);
    $assignPermission = $db->prepare(
        'INSERT IGNORE INTO role_permissions (role_id, permission_id, created_at) VALUES (:role_id, :permission_id, NOW())'
    );

    foreach ($permissionIds as $permissionId) {
        $assignPermission->execute(['role_id' => $superAdminRoleId, 'permission_id' => (int) $permissionId]);
    }

    $email = (string) env('SUPER_ADMIN_EMAIL', 'admin@example.com');
    $existing = $db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $existing->execute(['email' => strtolower($email)]);
    $userId = $existing->fetchColumn();

    if (! $userId) {
        $name = trim((string) env('SUPER_ADMIN_NAME', 'Super Admin'));
        [$firstName, $lastName] = array_pad(explode(' ', $name, 2), 2, 'Admin');
        $insertUser = $db->prepare(
            'INSERT INTO users (uuid, first_name, last_name, email, password_hash, status, email_verified_at, created_at, updated_at)
             VALUES (:uuid, :first_name, :last_name, :email, :password_hash, "active", NOW(), NOW(), NOW())'
        );
        $insertUser->execute([
            'uuid' => uuid_create_local(),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => strtolower($email),
            'password_hash' => Password::hash((string) env('SUPER_ADMIN_PASSWORD', 'Password@123')),
        ]);
        $userId = (int) $db->lastInsertId();
    }

    $assignRole = $db->prepare('INSERT IGNORE INTO user_roles (user_id, role_id, created_at) VALUES (:user_id, :role_id, NOW())');
    $assignRole->execute(['user_id' => (int) $userId, 'role_id' => $superAdminRoleId]);
};
