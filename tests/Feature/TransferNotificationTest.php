<?php

namespace Tests\Feature;

use App\Domains\Transfer\Contracts\NotifyTransferServiceInterface;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fake\FailingNotifyTransferService;
use Tests\TestCase;

class TransferNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(
            NotifyTransferServiceInterface::class,
            FailingNotifyTransferService::class
        );
    }

    public function test_transfer_succeeds_even_if_notification_fails()
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

        $this->assertEquals(50, $payer->fresh()->balance);
        $this->assertEquals(50, $payee->fresh()->balance);
    }
}
