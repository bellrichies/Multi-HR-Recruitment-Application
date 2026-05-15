<?php

declare(strict_types=1);

namespace App\Modules\Payments\Repositories;

use App\Core\Database;
use PDO;

class PaymentRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function create(array $data): array
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO payments (user_id, wallet_id, provider, internal_reference, amount, currency, status, purpose, metadata_json, created_at, updated_at)
             VALUES (:user_id, :wallet_id, :provider, :internal_reference, :amount, :currency, "pending", :purpose, :metadata_json, NOW(), NOW())'
        );
        $statement->execute([
            'user_id' => $data['user_id'],
            'wallet_id' => $data['wallet_id'],
            'provider' => $data['provider'],
            'internal_reference' => $data['internal_reference'],
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'purpose' => $data['purpose'],
            'metadata_json' => isset($data['metadata']) ? json_encode($data['metadata'], JSON_THROW_ON_ERROR) : null,
        ]);

        return $this->findById((int) $this->connection()->lastInsertId());
    }

    public function findByReference(string $reference): ?array
    {
        $statement = $this->connection()->prepare(
            'SELECT * FROM payments WHERE internal_reference = :reference OR provider_reference = :reference LIMIT 1'
        );
        $statement->execute(['reference' => $reference]);
        $payment = $statement->fetch();

        return is_array($payment) ? $payment : null;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM payments WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $payment = $statement->fetch();

        return is_array($payment) ? $payment : null;
    }

    public function markSuccessful(int $id, string $providerReference): array
    {
        $statement = $this->connection()->prepare(
            'UPDATE payments SET status = "successful", provider_reference = :provider_reference, verified_at = NOW(), updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute(['id' => $id, 'provider_reference' => $providerReference]);

        return $this->findById($id);
    }

    public function list(int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), 100);
        $total = (int) $this->connection()->query('SELECT COUNT(*) FROM payments')->fetchColumn();
        $offset = ($page - 1) * $perPage;
        $statement = $this->connection()->query("SELECT * FROM payments ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}");

        return [
            'data' => $statement->fetchAll(),
            'meta' => ['current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int) max(1, ceil($total / $perPage))],
        ];
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
