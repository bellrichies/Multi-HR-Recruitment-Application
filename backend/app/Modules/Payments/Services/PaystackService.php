<?php

declare(strict_types=1);

namespace App\Modules\Payments\Services;

use App\Core\HttpException;

class PaystackService
{
    public function initialize(string $email, float $amount, string $reference): array
    {
        if (empty(config('services.paystack.secret_key'))) {
            return [
                'authorization_url' => null,
                'access_code' => null,
                'reference' => $reference,
                'mode' => 'local_stub',
            ];
        }

        return $this->request('POST', '/transaction/initialize', [
            'email' => $email,
            'amount' => (int) round($amount * 100),
            'reference' => $reference,
            'callback_url' => config('services.paystack.callback_url'),
        ])['data'] ?? [];
    }

    public function verify(string $reference): array
    {
        if (empty(config('services.paystack.secret_key'))) {
            return ['reference' => $reference, 'status' => 'success'];
        }

        return $this->request('GET', '/transaction/verify/' . rawurlencode($reference))['data'] ?? [];
    }

    public function webhookValid(string $payload, ?string $signature): bool
    {
        $secret = (string) config('services.paystack.webhook_secret') ?: (string) config('services.paystack.secret_key');

        if ($secret === '') {
            return true;
        }

        return is_string($signature) && hash_equals(hash_hmac('sha512', $payload, $secret), $signature);
    }

    private function request(string $method, string $path, array $payload = []): array
    {
        $ch = curl_init('https://api.paystack.co' . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . config('services.paystack.secret_key'), 'Content-Type: application/json'],
        ]);

        if ($payload !== []) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_THROW_ON_ERROR));
        }

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $status >= 400) {
            throw new HttpException('Paystack request failed.', 502);
        }

        return json_decode((string) $response, true) ?: [];
    }
}
