<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_a_common_user(): void
    {
        $response = $this->postJson('/api/users', [
            'name' => 'Fulano',
            'email' => 'fulano@mail.com',
            'document' => '12345678900',
            'password' => '123456',
            'type' => 'USER',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'email' => 'fulano@mail.com',
                'type' => 'USER',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'fulano@mail.com',
        ]);

        $this->assertDatabaseHas('wallets', [
            'balance' => 100,
        ]);
    }
}
