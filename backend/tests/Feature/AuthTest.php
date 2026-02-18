<?php

use App\Models\User;

/*
 * Sanctum SPA auth requires requests from a stateful domain so the
 * session middleware is applied. We send the Origin header to satisfy this.
 */
function statefulPost(mixed $testCase, string $uri, array $data = []): \Illuminate\Testing\TestResponse
{
    return $testCase
        ->withHeaders(['Origin' => 'http://localhost'])
        ->postJson($uri, $data);
}

describe('POST /api/login', function () {
    it('logs in with valid credentials', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = statefulPost($this, '/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'name', 'email']])
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', 'test@example.com');
    });

    it('returns user resource in standard data wrapper', function () {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = statefulPost($this, '/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'created_at', 'updated_at'],
            ]);

        expect($response->json())->toHaveKey('data');
    });

    it('rejects invalid credentials', function () {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = statefulPost($this, '/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    });

    it('validates required fields', function () {
        $response = statefulPost($this, '/api/login', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    });

    it('validates email format', function () {
        $response = statefulPost($this, '/api/login', [
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    });

    it('does not expose password or remember_token in response', function () {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = statefulPost($this, '/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk();
        expect($response->json('data'))->not->toHaveKeys(['password', 'remember_token']);
    });
});

describe('POST /api/logout', function () {
    it('logs out an authenticated user', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeaders(['Origin' => 'http://localhost'])
            ->postJson('/api/logout');

        $response->assertOk()
            ->assertJsonPath('message', 'Logged out successfully.');
    });

    it('rejects unauthenticated logout', function () {
        $response = $this->postJson('/api/logout');

        $response->assertUnauthorized();
    });
});

describe('GET /api/user', function () {
    it('returns authenticated user profile', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/user');

        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'name', 'email']])
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    });

    it('returns user resource in standard data wrapper', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/user');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'created_at', 'updated_at'],
            ]);
    });

    it('rejects unauthenticated request', function () {
        $response = $this->getJson('/api/user');

        $response->assertUnauthorized();
    });
});
