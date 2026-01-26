<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransferIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_transfer_is_idempotent()
    {
        $payer = User::factory()->create([
            'type' => 'common',
            'balance' => 100,
        ]);

        $payee = User::factory()->create([
            'type' => 'common',
            'balance' => 0,
        ]);

        $payload = [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
            'amount' => 50,
            'idempotency_key' => '11111111-1111-1111-1111-111111111111',
        ];

        $this->postJson('/api/transfers', $payload)->assertCreated();
        $this->postJson('/api/transfers', $payload)
            ->assertStatus(422)
            ->assertJson([
                'error' => 'Idempotency key already used',
            ]);

        $this->assertDatabaseCount('transfers', 1);

        $this->assertEquals(50, $payer->fresh()->balance);
        $this->assertEquals(50, $payee->fresh()->balance);
    }
}
