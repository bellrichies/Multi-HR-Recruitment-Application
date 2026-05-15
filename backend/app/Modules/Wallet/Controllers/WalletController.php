<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Modules\Payments\Resources\PaymentResource;
use App\Modules\Payments\Services\PaymentService;
use App\Modules\Wallet\Requests\FundWalletRequest;
use App\Modules\Wallet\Resources\WalletResource;
use App\Modules\Wallet\Resources\WalletTransactionResource;
use App\Modules\Wallet\Services\WalletService;

class WalletController extends Controller
{
    public function __construct(
        private readonly WalletService $wallets,
        private readonly PaymentService $payments,
        private readonly FundWalletRequest $fundRequest
    ) {
    }

    public function show(Request $request): void
    {
        $this->success(WalletResource::make($this->wallets->getOrCreate((int) $request->user()['id'])), 'Wallet retrieved successfully.');
    }

    public function fund(Request $request): void
    {
        $data = $this->fundRequest->validate($request);
        $result = $this->payments->initializeWalletFunding($request->user(), (float) $data['amount'], $this->context($request));

        $this->success([
            'payment' => PaymentResource::make($result['payment']),
            'provider' => $result['provider'],
        ], 'Wallet funding initialized successfully.', [], 201);
    }

    public function transactions(Request $request): void
    {
        $result = $this->wallets->transactions((int) $request->user()['id'], (int) $request->query('page', 1), (int) $request->query('per_page', 20));

        $this->success(WalletTransactionResource::collection($result['data']), 'Wallet transactions retrieved successfully.', $result['meta']);
    }

    private function context(Request $request): array
    {
        return ['actor_id' => $request->user()['id'] ?? null, 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent()];
    }
}
