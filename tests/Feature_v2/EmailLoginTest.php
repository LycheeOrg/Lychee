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

namespace Tests\Feature_v2;

use App\Models\Configs;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 091 – signing in with the email address (FR-091-02 … FR-091-06).
 */
class EmailLoginTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		// The login route is throttled per IP; the array cache store is per test
		// but the fixtures of the base class may already have touched it.
		Cache::flush();
	}

	public function tearDown(): void
	{
		Configs::set('email_login_enabled', '0');
		parent::tearDown();
	}

	/**
	 * S-091-01 – with the setting off, the username still works.
	 */
	public function testUsernameLoginWithSettingOff(): void
	{
		Configs::set('email_login_enabled', '0');

		$this->assertNoContent($this->postJson('Auth::login', [
			'username' => $this->userMayUpload1->username,
			'password' => 'password',
		]));
		$this->assertAuthenticatedAs($this->userMayUpload1);
	}

	/**
	 * S-091-02 – with the setting off, the email is rejected.
	 */
	public function testEmailLoginWithSettingOff(): void
	{
		Configs::set('email_login_enabled', '0');

		$this->assertUnauthorized($this->postJson('Auth::login', [
			'username' => $this->userMayUpload1->email,
			'password' => 'password',
		]));
		$this->assertGuest();
	}

	/**
	 * S-091-03 – with the setting on, the username still works.
	 */
	public function testUsernameLoginWithSettingOn(): void
	{
		Configs::set('email_login_enabled', '1');

		$this->assertNoContent($this->postJson('Auth::login', [
			'username' => $this->userMayUpload1->username,
			'password' => 'password',
		]));
		$this->assertAuthenticatedAs($this->userMayUpload1);
	}

	/**
	 * S-091-04 – with the setting on, the email authenticates the same user.
	 */
	public function testEmailLoginWithSettingOn(): void
	{
		Configs::set('email_login_enabled', '1');

		$this->assertNoContent($this->postJson('Auth::login', [
			'username' => $this->userMayUpload1->email,
			'password' => 'password',
		]));
		$this->assertAuthenticatedAs($this->userMayUpload1);
	}

	/**
	 * S-091-05, S-091-06, NFR-091-03 – a wrong password, a known email with a
	 * wrong password and an unknown identifier are indistinguishable.
	 */
	public function testFailuresAreIndistinguishable(): void
	{
		Configs::set('email_login_enabled', '1');

		$by_username = $this->postJson('Auth::login', [
			'username' => $this->userMayUpload1->username,
			'password' => 'wrong_password',
		]);
		$by_email = $this->postJson('Auth::login', [
			'username' => $this->userMayUpload1->email,
			'password' => 'wrong_password',
		]);
		$unknown = $this->postJson('Auth::login', [
			'username' => 'nobody@example.com',
			'password' => 'wrong_password',
		]);

		$this->assertUnauthorized($by_username);
		$this->assertUnauthorized($by_email);
		$this->assertUnauthorized($unknown);
		self::assertSame($by_username->json('message'), $by_email->json('message'));
		self::assertSame($by_username->json('message'), $unknown->json('message'));
		$this->assertGuest();
	}

	/**
	 * S-091-07 – an account without an email keeps signing in with its username.
	 */
	public function testAccountWithoutEmailUsesItsUsername(): void
	{
		Configs::set('email_login_enabled', '1');
		$this->userNoUpload->email = null;
		$this->userNoUpload->save();

		$this->assertNoContent($this->postJson('Auth::login', [
			'username' => $this->userNoUpload->username,
			'password' => 'password',
		]));
		$this->assertAuthenticatedAs($this->userNoUpload);
	}

	/**
	 * S-091-08 – a username equal to another account's email resolves to the
	 * username owner, because the username is attempted first.
	 */
	public function testUsernameWinsOverAnotherAccountsEmail(): void
	{
		Configs::set('email_login_enabled', '1');

		$shared = 'shared-identifier@example.com';
		$this->userMayUpload2->email = $shared;
		$this->userMayUpload2->save();

		/** @var User $impostor */
		$impostor = User::factory()->create(['username' => $shared]);

		$this->assertNoContent($this->postJson('Auth::login', [
			'username' => $shared,
			'password' => 'password',
		]));
		$this->assertAuthenticatedAs($impostor);
	}

	/**
	 * S-091-14 – the flag reaches the frontend through the init payload.
	 */
	public function testInitExposesTheFlag(): void
	{
		Configs::set('email_login_enabled', '1');
		$response = $this->getJson('Gallery::Init');
		$this->assertOk($response);
		$response->assertJson(['is_email_login_enabled' => true]);

		Configs::set('email_login_enabled', '0');
		$response = $this->getJson('Gallery::Init');
		$this->assertOk($response);
		$response->assertJson(['is_email_login_enabled' => false]);
	}
}
