<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Unit\Http\Controllers;

use App\DTO\LdapUser;
use App\Exceptions\LdapConnectionException;
use App\Exceptions\UnauthenticatedException;
use App\Http\Controllers\AuthController;
use App\Http\Requests\Session\LoginRequest;
use App\Models\User;
use App\Services\Auth\LdapService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Tests\AbstractTestCase;
use Tests\Constants\FreeVerifyier;
use Tests\Constants\SupporterVerifyier;

/**
 * Unit tests for AuthController LDAP functionality.
 *
 * Tests LDAP authentication flow and fallback to local auth.
 * These tests focus on the local authentication fallback behavior.
 * Full LDAP integration is tested in Feature tests.
 */
class AuthControllerTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private AuthController $controller;
	private User $localUser;

	protected function setUp(): void
	{
		parent::setUp();

		$this->controller = new AuthController();

		// Create a local user for fallback tests
		$this->localUser = new User();
		$this->localUser->username = 'localuser';
		$this->localUser->password = Hash::make('localpassword');
		$this->localUser->email = 'local@example.com';
		$this->localUser->display_name = 'Local User';
		$this->localUser->may_upload = true;
		$this->localUser->may_edit_own_settings = true;
		$this->localUser->may_administrate = false;
		$this->localUser->save();
	}

	public function testLoginWithLdapDisabled(): void
	{
		Log::shouldReceive('channel')->andReturnSelf();
		Log::shouldReceive('notice')->once();

		// Disable LDAP
		Config::set('ldap.auth.enabled', false);

		// Mock request
		$request = \Mockery::mock(LoginRequest::class);
		$request->shouldReceive('username')->andReturn('localuser');
		$request->shouldReceive('password')->andReturn('localpassword');
		$request->shouldReceive('ip')->andReturn('127.0.0.1');
		// Not clean: totally a work around, verify should return a Verify interface.
		$request->shouldReceive('verify')->andReturn(new SupporterVerifyier());
		$request->shouldReceive('rememberMe')->andReturn(false);

		// Login should succeed with local auth
		$this->controller->login($request);

		// Verify user is authenticated
		$this->assertTrue(Auth::check());
		$this->assertSame('localuser', Auth::user()->username);
	}

	public function testLoginWithLocalCredentials(): void
	{
		Log::shouldReceive('channel')->andReturnSelf();
		Log::shouldReceive('notice')->once();

		// Disable LDAP to test local-only
		Config::set('ldap.auth.enabled', false);

		// Mock request
		$request = \Mockery::mock(LoginRequest::class);
		$request->shouldReceive('username')->andReturn('localuser');
		$request->shouldReceive('password')->andReturn('localpassword');
		$request->shouldReceive('ip')->andReturn('127.0.0.1');
		// Not clean: totally a work around, verify should return a Verify interface.
		$request->shouldReceive('verify')->andReturn(new FreeVerifyier());
		$request->shouldReceive('rememberMe')->andReturn(false);

		// Login should succeed with local auth
		$this->controller->login($request);

		// Verify user is authenticated
		$this->assertTrue(Auth::check());
		$this->assertSame('localuser', Auth::user()->username);
	}

	public function testLoginFailsWithInvalidCredentials(): void
	{
		Log::shouldReceive('channel')->andReturnSelf();
		Log::shouldReceive('error')->once();

		// Disable LDAP
		Config::set('ldap.auth.enabled', false);

		// Mock request with invalid credentials
		$request = \Mockery::mock(LoginRequest::class);
		$request->shouldReceive('username')->andReturn('invaliduser');
		$request->shouldReceive('password')->andReturn('wrongpassword');
		$request->shouldReceive('ip')->andReturn('127.0.0.1');
		$request->shouldReceive('verify')->andReturn(new FreeVerifyier());
		$request->shouldReceive('rememberMe')->andReturn(false);

		// Expect authentication exception
		$this->expectException(UnauthenticatedException::class);
		$this->expectExceptionMessage('Unknown user or invalid password');

		$this->controller->login($request);
	}

	public function testLogout(): void
	{
		// Login a user first
		Auth::login($this->localUser);
		$this->assertTrue(Auth::check());

		// Logout
		$this->controller->logout();

		// Verify user is logged out
		$this->assertFalse(Auth::check());
	}

	public function testGetCurrentUserWhenAuthenticated(): void
	{
		// Login a user
		Auth::login($this->localUser);

		// Get current user
		$resource = $this->controller->getCurrentUser();

		// Verify user resource
		$this->assertNotNull($resource);
	}

	public function testGetCurrentUserWhenNotAuthenticated(): void
	{
		// Ensure no user is authenticated
		Auth::logout();

		// Get current user (should return null user)
		$resource = $this->controller->getCurrentUser();

		// Verify resource is returned (but user is null inside)
		$this->assertNotNull($resource);
	}

	public function testGetConfig(): void
	{
		// Get auth config
		$config = $this->controller->getConfig();

		// Verify config is returned
		$this->assertNotNull($config);
	}

	public function testLoginWithLdapSuccessful(): void
	{
		// Mock login channel logs
		Log::shouldReceive('channel')->with('login')->andReturnSelf();
		Log::shouldReceive('notice')->zeroOrMoreTimes();

		// Mock default channel logs (used by ProvisionLdapUser)
		Log::shouldReceive('debug')->zeroOrMoreTimes();
		Log::shouldReceive('info')->zeroOrMoreTimes();
		Log::shouldReceive('error')->never();
		Log::shouldReceive('warning')->never();

		// Enable LDAP
		Config::set('ldap.auth.enabled', true);
		$this->setUpLdapConfig();

		// Create mock LDAP service
		$ldapService = \Mockery::mock(LdapService::class);

		$ldapUser = new LdapUser(
			username: 'ldapuser',
			user_dn: 'uid=ldapuser,ou=users,dc=example,dc=com',
			email: 'ldap@example.com',
			display_name: 'LDAP User'
		);

		$ldapService->shouldReceive('authenticate')
			->once()
			->with('ldapuser', 'ldappassword')
			->andReturn($ldapUser);

		$ldapService->shouldReceive('queryGroups')
			->once()
			->andReturn([]);

		$ldapService->shouldReceive('isUserInAdminGroup')
			->once()
			->andReturn(false);

		// Create controller that returns our mocked service
		$controller = new class($ldapService) extends AuthController {
			private LdapService $mockService;

			public function __construct(LdapService $mockService)
			{
				$this->mockService = $mockService;
			}

			protected function isLdapEnabled(HttpFoundationRequest $request): bool
			{
				return true;
			}

			protected function getLdapService(): LdapService
			{
				return $this->mockService;
			}
		};

		// Mock request
		$request = \Mockery::mock(LoginRequest::class);
		$request->shouldReceive('username')->andReturn('ldapuser');
		$request->shouldReceive('password')->andReturn('ldappassword');
		$request->shouldReceive('ip')->andReturn('127.0.0.1');
		// Not clean: totally a work around, verify should return a Verify interface.
		$request->shouldReceive('verify')->andReturn(new SupporterVerifyier());
		$request->shouldReceive('rememberMe')->andReturn(false);

		// Login should succeed via LDAP
		$controller->login($request);

		// Verify user is authenticated
		$this->assertTrue(Auth::check());
		$this->assertSame('ldapuser', Auth::user()->username);
		$this->assertSame('ldap@example.com', Auth::user()->email);
	}

	public function testLoginWithLdapFailureFallsBackToLocal(): void
	{
		Log::shouldReceive('channel')->andReturnSelf();
		Log::shouldReceive('notice')->once();
		Log::shouldReceive('debug')->zeroOrMoreTimes();
		Log::shouldReceive('error')->never();
		Log::shouldReceive('warning')->never();

		// Enable LDAP
		$this->setUpLdapConfig();

		// Create mock LDAP service that returns null (auth failed)
		$ldapService = \Mockery::mock(LdapService::class);

		$ldapService->shouldReceive('authenticate')
			->once()
			->with('localuser', 'localpassword')
			->andReturn(null);

		// Create controller that returns our mocked service
		$controller = new class($ldapService) extends AuthController {
			private LdapService $mockService;

			public function __construct(LdapService $mockService)
			{
				$this->mockService = $mockService;
			}

			protected function isLdapEnabled(HttpFoundationRequest $request): bool
			{
				return true;
			}

			protected function getLdapService(): LdapService
			{
				return $this->mockService;
			}
		};

		// Mock request
		$request = \Mockery::mock(LoginRequest::class);
		$request->shouldReceive('username')->andReturn('localuser');
		$request->shouldReceive('password')->andReturn('localpassword');
		$request->shouldReceive('ip')->andReturn('127.0.0.1');
		$request->shouldReceive('verify')->andReturn(new SupporterVerifyier());
		$request->shouldReceive('rememberMe')->andReturn(false);

		// Login should fall back to local auth
		$controller->login($request);

		// Verify user is authenticated via local auth
		$this->assertTrue(Auth::check());
		$this->assertSame('localuser', Auth::user()->username);
	}

	public function testLoginWithLdapConnectionErrorFallsBackToLocal(): void
	{
		Log::shouldReceive('channel')->andReturnSelf();
		Log::shouldReceive('warning')->once();
		Log::shouldReceive('notice')->once();
		Log::shouldReceive('error')->once();

		// Enable LDAP
		$this->setUpLdapConfig();

		// Create mock LDAP service that throws connection exception
		$ldapService = \Mockery::mock(LdapService::class);

		$ldapService->shouldReceive('authenticate')
			->once()
			->andThrow(new LdapConnectionException('LDAP server unreachable'));

		// Create controller that returns our mocked service
		$controller = new class($ldapService) extends AuthController {
			private LdapService $mockService;

			public function __construct(LdapService $mockService)
			{
				$this->mockService = $mockService;
			}

			protected function isLdapEnabled(HttpFoundationRequest $request): bool
			{
				return true;
			}

			protected function getLdapService(): LdapService
			{
				return $this->mockService;
			}
		};

		// Mock request
		$request = \Mockery::mock(LoginRequest::class);
		$request->shouldReceive('username')->andReturn('localuser');
		$request->shouldReceive('password')->andReturn('localpassword');
		$request->shouldReceive('ip')->andReturn('127.0.0.1');
		// Not clean: totally a work around, verify should return a Verify interface.
		$request->shouldReceive('verify')->andReturn(new SupporterVerifyier());
		$request->shouldReceive('rememberMe')->andReturn(false);

		// Login should fall back to local auth
		$controller->login($request);

		// Verify user is authenticated via local auth
		$this->assertTrue(Auth::check());
		$this->assertSame('localuser', Auth::user()->username);
	}

	public function testLoginFailsWhenBothLdapAndLocalFail(): void
	{
		Log::shouldReceive('channel')->andReturnSelf();
		Log::shouldReceive('error')->once();
		Log::shouldReceive('warning')->never();
		Log::shouldReceive('notice')->never();
		Log::shouldReceive('debug')->zeroOrMoreTimes();

		// Enable LDAP
		$this->setUpLdapConfig();

		// Create mock LDAP service that returns null
		$ldapService = \Mockery::mock(LdapService::class);

		$ldapService->shouldReceive('authenticate')
			->once()
			->andReturn(null);

		// Create controller that returns our mocked service
		$controller = new class($ldapService) extends AuthController {
			private LdapService $mockService;

			public function __construct(LdapService $mockService)
			{
				$this->mockService = $mockService;
			}

			protected function isLdapEnabled(HttpFoundationRequest $request): bool
			{
				return true;
			}

			protected function getLdapService(): LdapService
			{
				return $this->mockService;
			}
		};

		// Mock request with invalid credentials
		$request = \Mockery::mock(LoginRequest::class);
		$request->shouldReceive('username')->andReturn('invaliduser');
		$request->shouldReceive('password')->andReturn('wrongpassword');
		$request->shouldReceive('ip')->andReturn('127.0.0.1');
		// Not clean: totally a work around, verify should return a Verify interface.
		$request->shouldReceive('verify')->andReturn(new SupporterVerifyier());
		$request->shouldReceive('rememberMe')->andReturn(false);

		// Expect authentication exception
		$this->expectException(UnauthenticatedException::class);
		$this->expectExceptionMessage('Unknown user or invalid password');

		$controller->login($request);
	}

	public function testLoginWithLdapProvisioningError(): void
	{
		Log::shouldReceive('channel')->andReturnSelf();
		Log::shouldReceive('warning')->once();
		Log::shouldReceive('notice')->once();
		Log::shouldReceive('error')->never();
		Log::shouldReceive('debug')->zeroOrMoreTimes();

		// Enable LDAP
		$this->setUpLdapConfig();

		// Create mock LDAP service that authenticates but provisioning fails
		$ldapService = \Mockery::mock(LdapService::class);

		$ldapUser = new LdapUser(
			username: 'ldapuser',
			user_dn: 'uid=ldapuser,ou=users,dc=example,dc=com',
			email: 'ldap@example.com',
			display_name: 'LDAP User'
		);

		$ldapService->shouldReceive('authenticate')
			->once()
			->andReturn($ldapUser);

		// queryGroups throws an exception (simulating provisioning error)
		$ldapService->shouldReceive('queryGroups')
			->once()
			->andThrow(new \Exception('Provisioning failed'));

		// Create controller that returns our mocked service
		$controller = new class($ldapService) extends AuthController {
			private LdapService $mockService;

			public function __construct(LdapService $mockService)
			{
				$this->mockService = $mockService;
			}

			protected function isLdapEnabled(HttpFoundationRequest $request): bool
			{
				return true;
			}

			protected function getLdapService(): LdapService
			{
				return $this->mockService;
			}
		};

		// Mock request
		$request = \Mockery::mock(LoginRequest::class);
		$request->shouldReceive('username')->andReturn('localuser');
		$request->shouldReceive('password')->andReturn('localpassword');
		$request->shouldReceive('ip')->andReturn('127.0.0.1');
		// Not clean: totally a work around, verify should return a Verify interface.
		$request->shouldReceive('verify')->andReturn(new SupporterVerifyier());
		$request->shouldReceive('rememberMe')->andReturn(false);

		// Login should fall back to local auth
		$controller->login($request);

		// Verify user is authenticated via local auth
		$this->assertTrue(Auth::check());
		$this->assertSame('localuser', Auth::user()->username);
	}

	public function testLoginWithLdapEnabledButNotSupporter(): void
	{
		Log::shouldReceive('channel')->andReturnSelf();
		Log::shouldReceive('notice')->once();
		Log::shouldReceive('error')->never();
		Log::shouldReceive('warning')->never();
		Log::shouldReceive('debug')->zeroOrMoreTimes();

		// Enable LDAP
		$this->setUpLdapConfig();

		// Mock request (not a supporter, so LDAP won't be tried)
		$request = \Mockery::mock(LoginRequest::class);
		$request->shouldReceive('username')->andReturn('localuser');
		$request->shouldReceive('password')->andReturn('localpassword');
		$request->shouldReceive('ip')->andReturn('127.0.0.1');
		$request->shouldReceive('verify')->andReturn(new FreeVerifyier());
		$request->shouldReceive('rememberMe')->andReturn(false);

		// Login should go directly to local auth
		$this->controller->login($request);

		// Verify user is authenticated via local auth
		$this->assertTrue(Auth::check());
		$this->assertSame('localuser', Auth::user()->username);
	}

	/**
	 * Set up minimal LDAP configuration for tests.
	 */
	private function setUpLdapConfig(): void
	{
		Config::set('ldap.connections.default', [
			'hosts' => ['ldap.example.com'],
			'port' => 389,
			'base_dn' => 'dc=example,dc=com',
			'username' => 'cn=admin,dc=example,dc=com',
			'password' => 'adminpass',
		]);

		Config::set('ldap.auth', [
			'user_filter' => '(uid=%s)',
			'attributes' => [
				'username' => 'uid',
				'email' => 'mail',
				'display_name' => 'cn',
			],
		]);
	}

	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}
}
