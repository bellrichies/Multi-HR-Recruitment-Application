<?php

declare(strict_types=1);

namespace App\Modules\Payments\Resources;

class PaymentResource
{
    public static function make(array $payment): array
    {
        return [
            'id' => (int) $payment['id'],
            'user_id' => (int) $payment['user_id'],
            'wallet_id' => $payment['wallet_id'] === null ? null : (int) $payment['wallet_id'],
            'provider' => $payment['provider'],
            'provider_reference' => $payment['provider_reference'],
            'internal_reference' => $payment['internal_reference'],
            'amount' => (float) $payment['amount'],
            'currency' => $payment['currency'],
            'status' => $payment['status'],
            'purpose' => $payment['purpose'],
            'verified_at' => $payment['verified_at'],
            'created_at' => $payment['created_at'],
        ];
    }
}
