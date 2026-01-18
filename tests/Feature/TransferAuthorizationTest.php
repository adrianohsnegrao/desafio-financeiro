<?php

namespace Tests\Feature;

use App\Domains\Transfer\Contracts\AuthorizeTransferServiceInterface;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fake\DenyAuthorizeTransferService;
use Tests\TestCase;

class TransferAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(
            AuthorizeTransferServiceInterface::class,
            DenyAuthorizeTransferService::class
        );
    }

    public function test_transfer_is_denied_when_authorizer_fails()
    {
        $payer = User::factory()->create([
            'type' => 'common',
            'balance' => 100,
        ]);

        $payee = User::factory()->create([
            'type' => 'common',
            'balance' => 0,
        ]);

        $response = $this->postJson('/api/transfers', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
            'amount' => 50,
            'idempotency_key' => 'test-key-123',
        ]);

        $response->assertStatus(422);

        $this->assertEquals(100, $payer->fresh()->balance);
        $this->assertEquals(0, $payee->fresh()->balance);

        $this->assertDatabaseMissing('transfers', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
        ]);
    }
}
