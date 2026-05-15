<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Requests;

use App\Core\Request;
use App\Core\Validator;

class FundWalletRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function validate(Request $request): array
    {
        return $this->validator->validate($request->all(), [
            'amount' => 'required|numeric',
            'provider' => 'nullable|string|in:paystack',
            'purpose' => 'nullable|string|in:wallet_funding',
        ]);
    }
}
