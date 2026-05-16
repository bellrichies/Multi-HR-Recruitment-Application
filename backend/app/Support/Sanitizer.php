<?php

declare(strict_types=1);

namespace App\Support;

class Sanitizer
{
    public static function string(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? $value;

        return strip_tags($value);
    }

    public static function array(array $data, array $except = []): array
    {
        foreach ($data as $key => $value) {
            if (in_array((string) $key, $except, true)) {
                continue;
            }

            if (is_string($value)) {
                $data[$key] = self::string($value);
            } elseif (is_array($value)) {
                $data[$key] = self::array($value, $except);
            }
        }

        return $data;
    }
}
