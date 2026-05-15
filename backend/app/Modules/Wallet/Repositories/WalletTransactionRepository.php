<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Repositories;

use App\Core\Database;
use PDO;

class WalletTransactionRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function create(array $data): array
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO wallet_transactions
             (wallet_id, user_id, reference, transaction_type, direction, amount, balance_before, balance_after, status, description, metadata_json, created_at, updated_at)
             VALUES
             (:wallet_id, :user_id, :reference, :transaction_type, :direction, :amount, :balance_before, :balance_after, :status, :description, :metadata_json, NOW(), NOW())'
        );
        $statement->execute([
            'wallet_id' => $data['wallet_id'],
            'user_id' => $data['user_id'],
            'reference' => $data['reference'],
            'transaction_type' => $data['transaction_type'],
            'direction' => $data['direction'],
            'amount' => $data['amount'],
            'balance_before' => $data['balance_before'],
            'balance_after' => $data['balance_after'],
            'status' => $data['status'],
            'description' => $data['description'] ?? null,
            'metadata_json' => isset($data['metadata']) ? json_encode($data['metadata'], JSON_THROW_ON_ERROR) : null,
        ]);

        return $this->findById((int) $this->connection()->lastInsertId());
    }

    public function findByReference(string $reference): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM wallet_transactions WHERE reference = :reference LIMIT 1');
        $statement->execute(['reference' => $reference]);
        $transaction = $statement->fetch();

        return is_array($transaction) ? $transaction : null;
    }

    public function listForUser(int $userId, int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), 100);
        $count = $this->connection()->prepare('SELECT COUNT(*) FROM wallet_transactions WHERE user_id = :user_id');
        $count->execute(['user_id' => $userId]);
        $total = (int) $count->fetchColumn();
        $offset = ($page - 1) * $perPage;
        $statement = $this->connection()->prepare(
            "SELECT * FROM wallet_transactions WHERE user_id = :user_id ORDER BY created_at DESC, id DESC LIMIT {$perPage} OFFSET {$offset}"
        );
        $statement->execute(['user_id' => $userId]);

        return [
            'data' => $statement->fetchAll(),
            'meta' => ['current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int) max(1, ceil($total / $perPage))],
        ];
    }

    private function findById(int $id): array
    {
        $statement = $this->connection()->prepare('SELECT * FROM wallet_transactions WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);

        return $statement->fetch();
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
