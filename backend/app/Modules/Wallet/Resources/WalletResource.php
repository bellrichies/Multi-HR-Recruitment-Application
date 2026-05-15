<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Resources;

class WalletResource
{
    public static function make(array $wallet): array
    {
        return [
            'id' => (int) $wallet['id'],
            'user_id' => (int) $wallet['user_id'],
            'wallet_type' => $wallet['wallet_type'],
            'currency' => $wallet['currency'],
            'available_balance' => (float) $wallet['available_balance'],
            'ledger_balance' => (float) $wallet['ledger_balance'],
            'status' => $wallet['status'],
            'created_at' => $wallet['created_at'],
            'updated_at' => $wallet['updated_at'],
        ];
    }
}
