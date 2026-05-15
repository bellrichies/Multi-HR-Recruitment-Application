<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Resources;

class WalletTransactionResource
{
    public static function collection(array $transactions): array
    {
        return array_map(fn (array $transaction): array => [
            'id' => (int) $transaction['id'],
            'reference' => $transaction['reference'],
            'transaction_type' => $transaction['transaction_type'],
            'direction' => $transaction['direction'],
            'amount' => (float) $transaction['amount'],
            'balance_before' => (float) $transaction['balance_before'],
            'balance_after' => (float) $transaction['balance_after'],
            'status' => $transaction['status'],
            'description' => $transaction['description'],
            'created_at' => $transaction['created_at'],
        ], $transactions);
    }
}
