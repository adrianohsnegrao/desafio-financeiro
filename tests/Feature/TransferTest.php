<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TransferTest extends TestCase
{
    use RefreshDatabase;

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

        $response = $this->postJson('/api/transfers', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
            'amount' => 50,
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('transfers', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
            'amount' => 50,
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
        ]);

        $response->assertStatus(422);
    }
}
