<?php

namespace Tests\Feature_v2\User;

use Tests\Feature_v2\Base\BaseApiWithDataTest;

class RegisterTest extends BaseApiWithDataTest
{
    public function test_registration_requires_valid_data()
    {
        $response = $this->putJson('/Profile', []);
        $this->assertUnprocessable($response);
        $response->assertJsonValidationErrors(['username', 'email', 'password']);
    }

    public function test_registration_fails_with_duplicate_username()
    {
        $response = $this->putJson('/Profile', [
            'username' => $this->userMayUpload1->username,
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $this->assertStatus($response, 409);
    }

    public function test_registration_succeeds_with_valid_input()
    {
        $response = $this->putJson('/Profile', [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertCreated($response);
        $response->assertJson(['message' => 'User registered successfully']);
        $this->assertDatabaseHas('users', [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
        ]);
    }
}
