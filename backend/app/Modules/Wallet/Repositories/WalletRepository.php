<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Repositories;

use App\Core\Database;
use PDO;

class WalletRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function findByUserId(int $userId): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM wallets WHERE user_id = :user_id LIMIT 1');
        $statement->execute(['user_id' => $userId]);
        $wallet = $statement->fetch();

        return is_array($wallet) ? $wallet : null;
    }

    public function findByUserIdForUpdate(int $userId): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM wallets WHERE user_id = :user_id LIMIT 1 FOR UPDATE');
        $statement->execute(['user_id' => $userId]);
        $wallet = $statement->fetch();

        return is_array($wallet) ? $wallet : null;
    }

    public function create(int $userId, string $currency): array
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO wallets (user_id, wallet_type, currency, status, created_at, updated_at)
             VALUES (:user_id, "user", :currency, "active", NOW(), NOW())'
        );
        $statement->execute(['user_id' => $userId, 'currency' => $currency]);

        return $this->findByUserId($userId);
    }

    public function updateBalance(int $walletId, float $balance): void
    {
        $statement = $this->connection()->prepare(
            'UPDATE wallets SET available_balance = :balance, ledger_balance = :balance, updated_at = NOW() WHERE id = :id'
        );
        $statement->execute(['id' => $walletId, 'balance' => $balance]);
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
