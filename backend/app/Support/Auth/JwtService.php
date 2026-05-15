<?php

declare(strict_types=1);

namespace App\Support\Auth;

use App\Core\HttpException;

class JwtService
{
    public function generate(array $claims, ?int $ttl = null): string
    {
        $now = time();
        $payload = array_merge($claims, [
            'iat' => $now,
            'exp' => $now + ($ttl ?? (int) config('auth.jwt_ttl', 3600)),
            'jti' => bin2hex(random_bytes(16)),
        ]);

        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $segments = [
            $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR)),
            $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR)),
        ];
        $segments[] = $this->sign(implode('.', $segments));

        return implode('.', $segments);
    }

    public function parse(string $token): array
    {
        $segments = explode('.', $token);

        if (count($segments) !== 3) {
            throw new HttpException('Invalid token format.', 401);
        }

        [$encodedHeader, $encodedPayload, $signature] = $segments;
        $expected = $this->sign($encodedHeader . '.' . $encodedPayload);

        if (! hash_equals($expected, $signature)) {
            throw new HttpException('Invalid token signature.', 401);
        }

        $payload = json_decode($this->base64UrlDecode($encodedPayload), true);

        if (! is_array($payload)) {
            throw new HttpException('Invalid token payload.', 401);
        }

        if (($payload['exp'] ?? 0) < time()) {
            throw new HttpException('Token expired.', 401);
        }

        return $payload;
    }

    private function sign(string $payload): string
    {
        return $this->base64UrlEncode(hash_hmac(
            'sha256',
            $payload,
            (string) config('auth.jwt_secret'),
            true
        ));
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        $padding = strlen($value) % 4;

        if ($padding > 0) {
            $value .= str_repeat('=', 4 - $padding);
        }

        return base64_decode(strtr($value, '-_', '+/'), true) ?: '';
    }
}
