<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Services;

use App\Core\Database;
use App\Core\HttpException;
use App\Modules\Audit\Services\AuditLogService;
use App\Modules\Wallet\Repositories\WalletRepository;
use App\Modules\Wallet\Repositories\WalletTransactionRepository;

class WalletService
{
    public function __construct(
        private readonly WalletRepository $wallets,
        private readonly WalletTransactionRepository $transactions,
        private readonly AuditLogService $audit
    ) {
    }

    public function getOrCreate(int $userId): array
    {
        return $this->wallets->findByUserId($userId)
            ?? $this->wallets->create($userId, (string) config('wallet.currency', 'NGN'));
    }

    public function credit(int $userId, float $amount, string $type, string $reference, string $description, array $metadata = [], array $context = []): array
    {
        return $this->move($userId, $amount, 'credit', $type, $reference, $description, $metadata, $context);
    }

    public function debit(int $userId, float $amount, string $type, string $reference, string $description, array $metadata = [], array $context = []): array
    {
        return $this->move($userId, $amount, 'debit', $type, $reference, $description, $metadata, $context);
    }

    public function transactions(int $userId, int $page = 1, int $perPage = 20): array
    {
        return $this->transactions->listForUser($userId, $page, $perPage);
    }

    private function move(int $userId, float $amount, string $direction, string $type, string $reference, string $description, array $metadata, array $context): array
    {
        if ($amount <= 0) {
            throw new HttpException('Wallet amount must be greater than zero.', 422);
        }

        return Database::transaction(function () use ($userId, $amount, $direction, $type, $reference, $description, $metadata, $context): array {
            $existing = $this->transactions->findByReference($reference);

            if ($existing !== null) {
                return $existing;
            }

            $wallet = $this->wallets->findByUserIdForUpdate($userId) ?? $this->wallets->create($userId, (string) config('wallet.currency', 'NGN'));
            $before = (float) $wallet['available_balance'];
            $after = $direction === 'credit' ? $before + $amount : $before - $amount;

            if ($direction === 'debit' && $after < 0) {
                throw new HttpException('Insufficient wallet balance.', 422);
            }

            $this->wallets->updateBalance((int) $wallet['id'], $after);
            $transaction = $this->transactions->create([
                'wallet_id' => (int) $wallet['id'],
                'user_id' => $userId,
                'reference' => $reference,
                'transaction_type' => $type,
                'direction' => $direction,
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'status' => 'successful',
                'description' => $description,
                'metadata' => $metadata,
            ]);

            $this->audit->record([
                'actor_id' => $context['actor_id'] ?? $userId,
                'action' => 'wallet.' . $direction,
                'module' => 'wallet',
                'entity_type' => 'wallet_transaction',
                'entity_id' => (int) $transaction['id'],
                'new_values' => ['reference' => $reference, 'amount' => $amount, 'balance_after' => $after],
                'ip_address' => $context['ip_address'] ?? null,
                'user_agent' => $context['user_agent'] ?? null,
            ]);

            return $transaction;
        });
    }
}
