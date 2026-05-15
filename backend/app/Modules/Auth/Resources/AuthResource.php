<?php

declare(strict_types=1);

namespace App\Modules\Auth\Resources;

use App\Modules\Users\Resources\UserResource;

class AuthResource
{
    public static function token(array $user, array $roles, array $permissions, string $token, int $expiresIn): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $expiresIn,
            'user' => UserResource::make($user, $roles, $permissions),
        ];
    }
}
