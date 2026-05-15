<?php

declare(strict_types=1);

namespace App\Modules\Payments\Repositories;

use App\Core\Database;
use PDO;

class PaymentWebhookRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function createIfNew(string $provider, string $eventType, string $eventReference, array $payload): bool
    {
        $statement = $this->connection()->prepare(
            'INSERT IGNORE INTO payment_webhook_events (provider, event_type, event_reference, payload_json, created_at)
             VALUES (:provider, :event_type, :event_reference, :payload_json, NOW())'
        );
        $statement->execute([
            'provider' => $provider,
            'event_type' => $eventType,
            'event_reference' => $eventReference,
            'payload_json' => json_encode($payload, JSON_THROW_ON_ERROR),
        ]);

        return $statement->rowCount() === 1;
    }

    public function markProcessed(string $provider, string $eventType, string $eventReference): void
    {
        $statement = $this->connection()->prepare(
            'UPDATE payment_webhook_events SET processed_at = NOW()
             WHERE provider = :provider AND event_type = :event_type AND event_reference = :event_reference'
        );
        $statement->execute(['provider' => $provider, 'event_type' => $eventType, 'event_reference' => $eventReference]);
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
