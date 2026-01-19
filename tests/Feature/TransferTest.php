<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\Domains\Transfer\Contracts\AuthorizeTransferServiceInterface;
use Tests\Fake\DenyAuthorizeTransferService;


class TransferTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            '*/authorize' => Http::response([
                'data' => ['authorization' => true],
            ], 200),

            '*/notify' => Http::response([], 204),
        ]);
    }

    #[Test]
    public function user_can_transfer_money()
    {

        $payer = User::factory()->create([
            'type' => 'common',
            'balance' => 100,
        ]);

        $payee = User::factory()->create([
            'type' => 'common',
            'balance' => 0,
        ]);

        $idempotencyKey = fake()->uuid();

        $response = $this->postJson('/api/transfers', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
            'amount' => 50,
            'idempotency_key' => $idempotencyKey,
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('transfers', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
            'amount' => 50,
            'idempotency_key' => $idempotencyKey,
        ]);

        $this->assertEquals(50, $payer->fresh()->balance);
        $this->assertEquals(50, $payee->fresh()->balance);
    }

    #[Test]
    public function merchant_cannot_transfer_money()
    {
        $merchant = User::factory()->create([
            'type' => 'merchant',
            'balance' => 100,
        ]);

        $user = User::factory()->create([
            'type' => 'common',
            'balance' => 0,
        ]);

        $response = $this->postJson('/api/transfers', [
            'payer_id' => $merchant->id,
            'payee_id' => $user->id,
            'amount' => 10,
            'idempotency_key' => fake()->uuid(),
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function user_cannot_transfer_with_insufficient_balance()
    {
        $payer = User::factory()->create([
            'type' => 'common',
            'balance' => 10,
        ]);

        $payee = User::factory()->create([
            'type' => 'common',
            'balance' => 0,
        ]);

        $response = $this->postJson('/api/transfers', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
            'amount' => 50,
            'idempotency_key' => fake()->uuid(),
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function transfer_is_denied_when_authorizer_service_rejects()
    {
        $this->app->bind(
            AuthorizeTransferServiceInterface::class,
            DenyAuthorizeTransferService::class
        );

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
            'idempotency_key' => fake()->uuid(),
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
