<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modules\Payments\Services\PaystackService;
use PHPUnit\Framework\TestCase;

class WalletPaymentTest extends TestCase
{
    public function testLocalPaystackVerificationStubMatchesSuccessfulProviderShape(): void
    {
        $_ENV['PAYSTACK_SECRET_KEY'] = '';

        $verification = (new PaystackService())->verify('PAY-TEST-REFERENCE');

        $this->assertSame('PAY-TEST-REFERENCE', $verification['reference']);
        $this->assertSame('success', $verification['status']);
    }

    public function testPhaseSixPermissionsAreConfigured(): void
    {
        $permissions = config('permissions.permissions', []);

        $this->assertContains('wallet.view', $permissions);
        $this->assertContains('wallet.fund', $permissions);
        $this->assertContains('transactions.view', $permissions);
        $this->assertContains('payments.view', $permissions);
        $this->assertContains('candidates.unlock', $permissions);
    }
}
