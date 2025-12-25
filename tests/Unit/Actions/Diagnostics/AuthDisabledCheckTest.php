<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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

namespace Tests\Unit\Actions\Diagnostics;

use App\Actions\Diagnostics\Pipes\Checks\AuthDisabledCheck;
use App\DTO\DiagnosticData;
use App\DTO\DiagnosticDTO;
use App\Enum\MessageType;
use App\Models\User;
use App\Repositories\ConfigManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Tests\AbstractTestCase;
use Tests\Constants\FreeVerifyier;

class AuthDisabledCheckTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private AuthDisabledCheck $authDisabledCheck;
	private array $data;
	private \Closure $next;

	protected function setUp(): void
	{
		parent::setUp();
		$this->authDisabledCheck = new AuthDisabledCheck();
		$this->next = function (array $data) {
			return $data;
		};
	}

	/**
	 * Test handle method when required tables do not exist.
	 * Should return early without any checks.
	 *
	 * @return void
	 */
	public function testHandleWhenTablesDoNotExist(): void
	{
		Schema::shouldReceive('hasTable')->with('users')->andReturn(false);
		Schema::shouldReceive('hasTable')->with('oauth_credentials')->andReturn(true);
		Schema::shouldReceive('hasTable')->with('webauthn_credentials')->andReturn(true);

		$data = [];
		$result = $this->authDisabledCheck->handle($data, $this->next);

		$this->assertEmpty($result, 'Should return empty result when users table does not exist');
	}

	/**
	 * Test handle method when oauth_credentials table does not exist.
	 *
	 * @return void
	 */
	public function testHandleWhenOauthCredentialsTableDoesNotExist(): void
	{
		Schema::shouldReceive('hasTable')->with('users')->andReturn(true);
		Schema::shouldReceive('hasTable')->with('oauth_credentials')->andReturn(false);
		Schema::shouldReceive('hasTable')->with('webauthn_credentials')->andReturn(true);

		$data = [];
		$result = $this->authDisabledCheck->handle($data, $this->next);

		$this->assertEmpty($result, 'Should return empty result when oauth_credentials table does not exist');
	}

	/**
	 * Test handle method when webauthn_credentials table does not exist.
	 *
	 * @return void
	 */
	public function testHandleWhenWebAuthnCredentialsTableDoesNotExist(): void
	{
		Schema::shouldReceive('hasTable')->with('users')->andReturn(true);
		Schema::shouldReceive('hasTable')->with('oauth_credentials')->andReturn(true);
		Schema::shouldReceive('hasTable')->with('webauthn_credentials')->andReturn(false);

		$data = [];
		$result = $this->authDisabledCheck->handle($data, $this->next);

		$this->assertEmpty($result, 'Should return empty result when webauthn_credentials table does not exist');
	}

	/**
	 * Test handle method when basic auth is enabled.
	 * Should return early without checking other auth methods.
	 *
	 * @return void
	 */
	public function testHandleWhenBasicAuthEnabled(): void
	{
		Config::set('features.disable-basic-auth', false);

		$data = [];
		$result = $this->authDisabledCheck->handle($data, $this->next);

		$this->assertEmpty($result, 'Should return empty result when basic auth is enabled');
	}

	/**
	 * Test handle method when all authentication methods are disabled.
	 * Should add error diagnostic.
	 *
	 * @return void
	 */
	public function testHandleWhenAllAuthMethodsDisabled(): void
	{
		Config::set('features.disable-basic-auth', true);
		Config::set('features.disable-webauthn', true);

		$data = [];
		$result = $this->authDisabledCheck->handle($data, $this->next);

		$this->assertCount(1, $result);
		$this->assertInstanceOf(DiagnosticData::class, $result[0]);
		$this->assertEquals(MessageType::ERROR, $result[0]->type);
		$this->assertEquals('All authentication methods are disabled. Really?', $result[0]->message);
		$this->assertEquals(AuthDisabledCheck::class, $result[0]->from);
		$this->assertEquals([AuthDisabledCheck::INFO], $result[0]->details);
	}

	/**
	 * Test handle method when basic auth is disabled but OAuth is enabled with admin users.
	 * Should not add any error diagnostics.
	 *
	 * @return void
	 */
	public function testHandleWhenBasicAuthDisabledButOauthEnabledWithAdminUsers(): void
	{
		Config::set('features.disable-basic-auth', true);
		Config::set('features.disable-webauthn', true);
		Config::set('services.google', ['client_id' => 'test']);

		// Create admin user with OAuth credentials
		$adminUser = User::factory()->may_administrate()->create();
		$adminUser->oauthCredentials()->create([
			'token_id' => 'test-provider-id',
			'provider' => 'google',
		]);

		$data = [];
		$result = $this->authDisabledCheck->handle($data, $this->next);
		$this->assertEmpty($result, 'Should return empty result when OAuth is enabled with admin users');
	}

	/**
	 * Test handle method when basic auth is disabled, OAuth enabled but no admin users with OAuth.
	 * Should add error diagnostic.
	 *
	 * @return void
	 */
	public function testHandleWhenOauthEnabledButNoAdminUsersWithOauth(): void
	{
		Config::set('features.disable-basic-auth', true);
		Config::set('features.disable-webauthn', true);
		Config::set('services.google', ['client_id' => 'test']);

		// Create regular user with OAuth credentials (not admin)
		$regularUser = User::factory()->create(['may_administrate' => false]);
		$regularUser->oauthCredentials()->create([
			'token_id' => 'test-provider-id',
			'provider' => 'google',
		]);

		$data = [];
		$result = $this->authDisabledCheck->handle($data, $this->next);

		$this->assertCount(1, $result);
		$this->assertInstanceOf(DiagnosticData::class, $result[0]);
		$this->assertEquals(MessageType::ERROR, $result[0]->type);
		$this->assertEquals('Basic auth and Webauthn are disabled and there are no admin user with Oauth enabled.', $result[0]->message);
		$this->assertEquals(AuthDisabledCheck::class, $result[0]->from);
		$this->assertEquals([AuthDisabledCheck::INFO], $result[0]->details);
	}

	/**
	 * Test handle method with existing data in the array.
	 * Should preserve existing data and add new diagnostics.
	 *
	 * @return void
	 */
	public function testHandleWithExistingData(): void
	{
		$existingDiagnostic = DiagnosticData::info('Existing diagnostic', 'TestClass');

		Config::set('features.disable-basic-auth', true);
		Config::set('features.disable-webauthn', true);

		$data = [$existingDiagnostic];
		$result = $this->authDisabledCheck->handle($data, $this->next);

		$this->assertCount(2, $result);
		$this->assertEquals($existingDiagnostic, $result[0]); // Should preserve existing data
		$this->assertEquals(MessageType::ERROR, $result[1]->type);
		$this->assertEquals('All authentication methods are disabled. Really?', $result[1]->message);
	}

	/**
	 * Test that the closure next function is called and returns the modified data.
	 *
	 * @return void
	 */
	public function testNextClosureIsCalled(): void
	{
		$nextCalled = false;
		$nextFunction = function (array $data) use (&$nextCalled) {
			$nextCalled = true;

			return $data;
		};

		Config::set('features.disable-basic-auth', false);

		$data = [];
		$result = $this->authDisabledCheck->handle($data, $nextFunction);

		$this->assertTrue($nextCalled, 'Next closure should be called');
	}

	/**
	 * Test that the method passes data by reference correctly.
	 *
	 * @return void
	 */
	public function testDataPassedByReference(): void
	{
		Config::set('features.disable-basic-auth', true);
		Config::set('features.disable-webauthn', true);

		$originalData = [];
		$this->authDisabledCheck->handle($originalData, $this->next);

		$this->assertCount(1, $originalData, 'Original data array should be modified by reference');
	}

	/**
	 * Test the constant INFO message is correctly defined.
	 *
	 * @return void
	 */
	public function testInfoConstant(): void
	{
		$this->assertEquals(
			'You need to enable at least one authentication method to be able to use Lychee...',
			AuthDisabledCheck::INFO,
			'INFO constant should match expected message'
		);
	}
}