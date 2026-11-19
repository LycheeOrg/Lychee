<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v3\User;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Authorization header scheme handling of {@see \App\Services\Auth\SessionOrTokenGuard::getUserByToken()}.
 */
class ApiTokenSchemeTest extends BaseApiWithDataTest
{
	private const NO_BEARER_WARNING = 'Auth token found, but Bearer prefix not provided.';

	public function testBearerTokenAuthenticatesWithoutWarning(): void
	{
		$token = $this->resetToken($this->admin);
		config(['auth.token_guard.log_warn_no_scheme_bearer' => true]);
		Log::spy();

		$response = $this->getJson('Auth::user', headers: ['Authorization' => 'Bearer ' . $token]);

		$this->assertOk($response);
		$response->assertJson(['id' => $this->admin->id]);
		Log::shouldNotHaveReceived('warning', [self::NO_BEARER_WARNING]);
	}

	public function testRawTokenAuthenticatesAndWarns(): void
	{
		$token = $this->resetToken($this->admin);
		config(['auth.token_guard.log_warn_no_scheme_bearer' => true]);
		Log::spy();

		$response = $this->getJson('Auth::user', headers: ['Authorization' => $token]);

		$this->assertOk($response);
		$response->assertJson(['id' => $this->admin->id]);
		Log::shouldHaveReceived('warning')->with(self::NO_BEARER_WARNING)->once();
	}

	public function testBasicCredentialsAreIgnored(): void
	{
		$response = $this->getJson('Auth::user', headers: ['Authorization' => 'Basic ' . base64_encode('proxy:secret')]);

		$this->assertOk($response);
		$this->assertGuest();
	}

	public function testUnknownBearerTokenFailsWhenConfigured(): void
	{
		config(['auth.token_guard.fail_bearer_authenticable_not_found' => true]);

		$response = $this->getJson('Auth::user', headers: ['Authorization' => 'Bearer not-a-real-token']);

		$response->assertStatus(400);
	}

	public function testUnknownBearerTokenIsGuestWhenNotConfiguredToFail(): void
	{
		config(['auth.token_guard.fail_bearer_authenticable_not_found' => false]);

		$response = $this->getJson('Auth::user', headers: ['Authorization' => 'Bearer not-a-real-token']);

		$this->assertOk($response);
		$this->assertGuest();
	}

	/**
	 * Resets the token of the given user and returns it.
	 */
	private function resetToken(User $user): string
	{
		$response = $this->actingAs($user)->postJson('Profile::resetToken', []);
		$this->assertCreated($response);
		Auth::logout();
		Session::flush();

		return $response->json('token');
	}
}
