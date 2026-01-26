<?php

namespace Tests\Feature;

use App\Models\Domains\Transfer\Models\Transfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TransferCompatEndpointTest extends TestCase
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

    public function test_compat_endpoint_uses_idempotency_header()
    {
        $payer = User::factory()->create([
            'type' => 'common',
            'balance' => 100,
        ]);

        $payee = User::factory()->create([
            'type' => 'common',
            'balance' => 0,
        ]);

        $idempotencyKey = '11111111-1111-1111-1111-111111111111';

        $this->postJson(
            '/api/transfer',
            [
                'payer' => $payer->id,
                'payee' => $payee->id,
                'value' => 50,
            ],
            ['Idempotency-Key' => $idempotencyKey]
        )->assertCreated();

        $this->postJson(
            '/api/transfer',
            [
                'payer' => $payer->id,
                'payee' => $payee->id,
                'value' => 50,
            ],
            ['Idempotency-Key' => $idempotencyKey]
        )->assertStatus(422)
            ->assertJson([
                'error' => 'Idempotency key already used',
            ]);

        $this->assertDatabaseCount('transfers', 1);
        $this->assertDatabaseHas('transfers', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
            'amount' => 50,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    public function test_compat_endpoint_generates_idempotency_key_when_missing()
    {
        $payer = User::factory()->create([
            'type' => 'common',
            'balance' => 100,
        ]);

        $payee = User::factory()->create([
            'type' => 'common',
            'balance' => 0,
        ]);

        $this->postJson('/api/transfer', [
            'payer' => $payer->id,
            'payee' => $payee->id,
            'value' => 25,
        ])->assertCreated();

        $transfer = Transfer::first();

        $this->assertNotNull($transfer);
        $this->assertNotEmpty($transfer->idempotency_key);
    }

    public function test_compat_endpoint_rejects_transfer_to_self()
    {
        $payer = User::factory()->create([
            'type' => 'common',
            'balance' => 100,
        ]);

        $this->postJson('/api/transfer', [
            'payer' => $payer->id,
            'payee' => $payer->id,
            'value' => 25,
        ])->assertStatus(422);
    }
}
