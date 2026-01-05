<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_common_user_can_transfer_money(): void
    {
        Http::fake([
            '*/authorize' => Http::response([
                'data' => ['authorization' => true]
            ], 200),

            '*/notify' => Http::response(null, 204),
        ]);

        $payer = User::factory()->create(['type' => 'USER']);
        $payee = User::factory()->create(['type' => 'MERCHANT']);

        $payer->wallet()->create(['balance' => 100]);
        $payee->wallet()->create(['balance' => 0]);

        $response = $this->actingAs($payer, 'sanctum')
            ->postJson('/api/transactions', [
                'value' => 50,
                'payee_id' => $payee->id,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('transactions', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
            'value' => 50,
        ]);
    }

    public function test_merchant_cannot_transfer_money(): void
    {
        $merchant = User::factory()->create(['type' => 'MERCHANT']);
        $merchant->wallet()->create(['balance' => 100]);

        $response = $this->actingAs($merchant, 'sanctum')
            ->postJson('/api/transactions', [
                'value' => 10,
                'payee_id' => 1,
            ]);

        $response->assertStatus(403);
    }

    public function test_insufficient_balance(): void
    {
        $payer = User::factory()->create(['type' => 'USER']);
        $payee = User::factory()->create(['type' => 'USER']);

        $payer->wallet()->create(['balance' => 10]);
        $payee->wallet()->create(['balance' => 0]);

        $response = $this->actingAs($payer, 'sanctum')
            ->postJson('/api/transactions', [
                'value' => 50,
                'payee_id' => $payee->id,
            ]);

        $response->assertStatus(422);
    }

}
