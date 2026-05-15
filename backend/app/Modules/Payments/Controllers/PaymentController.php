<?php

declare(strict_types=1);

namespace App\Modules\Payments\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Modules\Payments\Resources\PaymentResource;
use App\Modules\Payments\Services\PaymentService;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments)
    {
    }

    public function callback(Request $request): void
    {
        $reference = (string) ($request->input('reference') ?? $request->query('reference'));
        $payment = $this->payments->verify($reference, $this->context($request));

        $this->success(PaymentResource::make($payment), 'Payment verified successfully.');
    }

    public function webhook(Request $request): void
    {
        $raw = $request->raw();
        $payload = json_decode($raw, true) ?: [];
        $result = $this->payments->webhook($payload, $raw, $request->header('X-Paystack-Signature'));

        $this->success($result, 'Webhook processed successfully.');
    }

    public function index(Request $request): void
    {
        $result = $this->payments->list((int) $request->query('page', 1), (int) $request->query('per_page', 20));

        $this->success(array_map(fn (array $payment): array => PaymentResource::make($payment), $result['data']), 'Payments retrieved successfully.', $result['meta']);
    }

    public function show(Request $request, string $id): void
    {
        $this->success(PaymentResource::make($this->payments->find((int) $id)), 'Payment retrieved successfully.');
    }

    private function context(Request $request): array
    {
        return ['actor_id' => $request->user()['id'] ?? null, 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent()];
    }
}
