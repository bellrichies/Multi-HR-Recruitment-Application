<?php

declare(strict_types=1);

namespace App\Modules\Payments\Services;

use App\Core\Database;
use App\Core\HttpException;
use App\Modules\Audit\Services\AuditLogService;
use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Payments\Repositories\PaymentRepository;
use App\Modules\Payments\Repositories\PaymentWebhookRepository;
use App\Modules\Wallet\Services\WalletService;

class PaymentService
{
    public function __construct(
        private readonly PaymentRepository $payments,
        private readonly PaymentWebhookRepository $webhooks,
        private readonly PaystackService $paystack,
        private readonly WalletService $wallets,
        private readonly AuditLogService $audit,
        private readonly NotificationService $notifications
    ) {
    }

    public function initializeWalletFunding(array $user, float $amount, array $context): array
    {
        $wallet = $this->wallets->getOrCreate((int) $user['id']);
        $reference = 'PAY-' . strtoupper(bin2hex(random_bytes(8)));
        $payment = $this->payments->create([
            'user_id' => (int) $user['id'],
            'wallet_id' => (int) $wallet['id'],
            'provider' => 'paystack',
            'internal_reference' => $reference,
            'amount' => $amount,
            'currency' => $wallet['currency'],
            'purpose' => 'wallet_funding',
            'metadata' => ['email' => $user['email']],
        ]);
        $provider = $this->paystack->initialize((string) $user['email'], $amount, $reference);

        return ['payment' => $payment, 'provider' => $provider];
    }

    public function verify(string $reference, array $context = []): array
    {
        $payment = $this->payments->findByReference($reference);

        if ($payment === null) {
            throw new HttpException('Payment not found.', 404);
        }

        if ($payment['status'] === 'successful') {
            return $payment;
        }

        $verification = $this->paystack->verify($reference);
        $status = $verification['status'] ?? null;
        $providerReference = (string) ($verification['reference'] ?? $reference);

        if ($status !== 'success') {
            $this->notifications->notify((int) $payment['user_id'], 'Payment failed', 'Payment verification failed. Please retry or contact support.', 'payment_failed', [
                'payment_id' => (int) $payment['id'],
                'reference' => $payment['internal_reference'],
            ]);
            throw new HttpException('Payment verification failed.', 422);
        }

        return Database::transaction(function () use ($payment, $providerReference, $context): array {
            $updated = $this->payments->markSuccessful((int) $payment['id'], $providerReference);
            $this->wallets->credit(
                (int) $payment['user_id'],
                (float) $payment['amount'],
                'wallet_funding',
                'WALLET-' . $payment['internal_reference'],
                'Wallet funding via Paystack.',
                ['payment_id' => (int) $payment['id']],
                $context
            );
            $this->audit->record([
                'actor_id' => $context['actor_id'] ?? (int) $payment['user_id'],
                'action' => 'payments.verify',
                'module' => 'payments',
                'entity_type' => 'payment',
                'entity_id' => (int) $payment['id'],
                'new_values' => ['status' => 'successful', 'provider_reference' => $providerReference],
            ]);
            $this->notifications->notify((int) $payment['user_id'], 'Wallet funded', 'Your wallet funding was successful.', 'wallet_funded', [
                'payment_id' => (int) $payment['id'],
                'amount' => (float) $payment['amount'],
                'currency' => $payment['currency'],
            ]);

            return $updated;
        });
    }

    public function webhook(array $payload, string $rawPayload, ?string $signature): array
    {
        if (! $this->paystack->webhookValid($rawPayload, $signature)) {
            throw new HttpException('Invalid Paystack webhook signature.', 401);
        }

        $eventType = (string) ($payload['event'] ?? 'unknown');
        $reference = (string) ($payload['data']['reference'] ?? '');

        if ($reference === '') {
            throw new HttpException('Webhook reference is missing.', 422);
        }

        if (! $this->webhooks->createIfNew('paystack', $eventType, $reference, $payload)) {
            return ['processed' => false, 'message' => 'Duplicate webhook ignored.'];
        }

        if ($eventType === 'charge.success') {
            $this->verify($reference);
        }

        $this->webhooks->markProcessed('paystack', $eventType, $reference);

        return ['processed' => true];
    }

    public function list(int $page = 1, int $perPage = 20): array
    {
        return $this->payments->list($page, $perPage);
    }

    public function find(int $id): array
    {
        $payment = $this->payments->findById($id);

        if ($payment === null) {
            throw new HttpException('Payment not found.', 404);
        }

        return $payment;
    }
}
