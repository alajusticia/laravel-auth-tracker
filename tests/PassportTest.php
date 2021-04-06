<?php

namespace ALajusticia\AuthTracker\Tests;

use Laravel\Passport\Client;

class PassportTest extends TestCase
{
    protected $passwordGrantClient;

    public function test_auth_with_passport()
    {
        $this->passwordGrantClient = Client::where('password_client', true)->first();

        // Ensure that the password grant client has the right provider
        $this->passwordGrantClient->provider = 'passport_users';
        $this->passwordGrantClient->save();

        // Create and authenticate user 1
        $response = $this->authenticate(PassportUser::factory()->create());
        $response->assertOk();

        $accessToken = $response->json('access_token');

        $response = $this->getJson('/api/check', [
            'Authorization' => 'Bearer '.$accessToken,
        ]);
        $response->assertOk();

        // Check that current login exists
        $this->assertNotEmpty($currentLogin = $response->json());
    }

    protected function authenticate($user)
    {
        // Authenticate user with Passport

        return $this->postJson('/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $this->passwordGrantClient->id,
            'client_secret' => $this->passwordGrantClient->secret,
            'username' => $user->email,
            'password' => 'password',
            'scope' => ''
        ]);
    }
}
