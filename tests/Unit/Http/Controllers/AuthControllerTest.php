<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Unit\Http\Controllers;

use App\Exceptions\UnauthenticatedException;
use App\Http\Controllers\AuthController;
use App\Http\Requests\Session\LoginRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tests\AbstractTestCase;

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
		$request->shouldReceive('verify->is_supporter')->andReturn(true);

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
		$request->shouldReceive('verify->is_supporter')->andReturn(false);

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
		$request->shouldReceive('verify->is_supporter')->andReturn(false);

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

	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}
}
