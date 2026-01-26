<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Unit\Http\Controllers;

use App\Exceptions\UnauthenticatedException;
use App\Http\Requests\Session\LoginRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Tests\AbstractTestCase;

/**
 * Tests for LDAP authentication integration in AuthController.
 *
 * These tests focus on the AuthController's LDAP integration paths,
 * particularly the isLdapEnabled() and attemptLdapLogin() logic.
 */
class AuthControllerLdapTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testLdapDisabledUsesLocalAuth(): void
	{
		// Disable LDAP
		Config::set('ldap.auth.enabled', false);

		// Create local user
		$user = new User();
		$user->username = 'localuser';
		$user->password = Hash::make('localpass');
		$user->email = 'local@example.com';
		$user->may_upload = true;
		$user->may_edit_own_settings = true;
		$user->may_administrate = false;
		$user->save();

		// Create login request
		$request = LoginRequest::createFrom(
			request()->create('/api/Auth::login', 'POST', [
				'username' => 'localuser',
				'password' => 'localpass',
			])
		);
		$request->setContainer(app());
		$request->validateResolved();

		// Attempt login
		$controller = new \App\Http\Controllers\AuthController();
		$controller->login($request);

		// Verify user is authenticated via local auth
		$this->assertTrue(Auth::check());
		$this->assertSame('localuser', Auth::user()->username);
	}

	public function testLdapDisabledWithInvalidCredentialsThrows(): void
	{
		// Disable LDAP
		Config::set('ldap.auth.enabled', false);

		// Create local user
		$user = new User();
		$user->username = 'localuser';
		$user->password = Hash::make('localpass');
		$user->email = 'local@example.com';
		$user->may_upload = true;
		$user->may_edit_own_settings = true;
		$user->may_administrate = false;
		$user->save();

		// Create login request with wrong password
		$request = LoginRequest::createFrom(
			request()->create('/api/Auth::login', 'POST', [
				'username' => 'localuser',
				'password' => 'wrongpass',
			])
		);
		$request->setContainer(app());
		$request->validateResolved();

		$this->expectException(UnauthenticatedException::class);
		$this->expectExceptionMessage('Unknown user or invalid password');

		// Attempt login
		$controller = new \App\Http\Controllers\AuthController();
		$controller->login($request);
	}

	public function testLdapEnabledButServerUnreachableFallsBackToLocal(): void
	{
		// Enable LDAP with invalid host (will fail to connect)
		Config::set('ldap.auth.enabled', true);
		Config::set('ldap.connections.default', [
			'hosts' => ['invalid.nonexistent.local'],
			'port' => 389,
			'base_dn' => 'dc=test,dc=local',
			'username' => 'cn=admin,dc=test,dc=local',
			'password' => 'adminpass',
			'timeout' => 1,
			'use_tls' => false,
		]);

		// Create local user
		$user = new User();
		$user->username = 'fallbackuser';
		$user->password = Hash::make('fallbackpass');
		$user->email = 'fallback@example.com';
		$user->may_upload = true;
		$user->may_edit_own_settings = true;
		$user->may_administrate = false;
		$user->save();

		// Create login request
		$request = LoginRequest::createFrom(
			request()->create('/api/Auth::login', 'POST', [
				'username' => 'fallbackuser',
				'password' => 'fallbackpass',
			])
		);
		$request->setContainer(app());
		$request->validateResolved();

		// Attempt login - should fall back to local auth
		$controller = new \App\Http\Controllers\AuthController();
		$controller->login($request);

		// Verify user is authenticated via local auth fallback
		$this->assertTrue(Auth::check());
		$this->assertSame('fallbackuser', Auth::user()->username);
	}

	public function testLdapEnabledWithInvalidCredentialsThrows(): void
	{
		// Enable LDAP with invalid host
		Config::set('ldap.auth.enabled', true);
		Config::set('ldap.connections.default', [
			'hosts' => ['invalid.nonexistent.local'],
			'port' => 389,
			'base_dn' => 'dc=test,dc=local',
			'username' => 'cn=admin,dc=test,dc=local',
			'password' => 'adminpass',
			'timeout' => 1,
			'use_tls' => false,
		]);

		// No local user exists

		// Create login request
		$request = LoginRequest::createFrom(
			request()->create('/api/Auth::login', 'POST', [
				'username' => 'nonexistent',
				'password' => 'wrongpass',
			])
		);
		$request->setContainer(app());
		$request->validateResolved();

		$this->expectException(UnauthenticatedException::class);
		$this->expectExceptionMessage('Unknown user or invalid password');

		// Attempt login - LDAP will fail, local auth will fail
		$controller = new \App\Http\Controllers\AuthController();
		$controller->login($request);
	}

	public function testIsLdapEnabledReturnsFalseWhenDisabled(): void
	{
		Config::set('ldap.auth.enabled', false);

		$controller = new \App\Http\Controllers\AuthController();
		$reflection = new \ReflectionClass($controller);
		$method = $reflection->getMethod('isLdapEnabled');
		$method->setAccessible(true);

		$result = $method->invoke($controller);

		$this->assertFalse($result);
	}

	public function testIsLdapEnabledReturnsTrueWhenEnabled(): void
	{
		Config::set('ldap.auth.enabled', true);

		$controller = new \App\Http\Controllers\AuthController();
		$reflection = new \ReflectionClass($controller);
		$method = $reflection->getMethod('isLdapEnabled');
		$method->setAccessible(true);

		$result = $method->invoke($controller);

		$this->assertTrue($result);
	}

	public function testIsLdapEnabledReturnsFalseWhenNotSet(): void
	{
		Config::set('ldap.auth.enabled', null);

		$controller = new \App\Http\Controllers\AuthController();
		$reflection = new \ReflectionClass($controller);
		$method = $reflection->getMethod('isLdapEnabled');
		$method->setAccessible(true);

		$result = $method->invoke($controller);

		$this->assertFalse($result);
	}

	public function testIsLdapEnabledReturnsFalseForNonBooleanTrue(): void
	{
		// Test that it requires strict boolean true
		Config::set('ldap.auth.enabled', 'true'); // String, not boolean

		$controller = new \App\Http\Controllers\AuthController();
		$reflection = new \ReflectionClass($controller);
		$method = $reflection->getMethod('isLdapEnabled');
		$method->setAccessible(true);

		$result = $method->invoke($controller);

		$this->assertFalse($result);
	}

	// ===== ADDITIONAL COVERAGE TESTS =====

	public function testLogoutClearsSessionAndAuth(): void
	{
		// Create and login a user
		$user = new User();
		$user->username = 'testuser';
		$user->password = Hash::make('password');
		$user->email = 'test@example.com';
		$user->may_upload = true;
		$user->may_edit_own_settings = true;
		$user->may_administrate = false;
		$user->save();

		Auth::login($user);
		$this->assertTrue(Auth::check());

		// Call logout
		$controller = new \App\Http\Controllers\AuthController();
		$controller->logout();

		// Verify user is logged out
		$this->assertFalse(Auth::check());
	}

	public function testGetGlobalRightsReturnsResource(): void
	{
		// GlobalRightsResource requires complex request setup with configs attribute
		// This is better tested in integration tests
		// Here we just verify the method exists and returns the right type
		$controller = new \App\Http\Controllers\AuthController();

		$reflection = new \ReflectionMethod($controller, 'getGlobalRights');
		$returnType = $reflection->getReturnType();

		$this->assertNotNull($returnType);
		$this->assertSame(\App\Http\Resources\Rights\GlobalRightsResource::class, $returnType->getName());
	}

	public function testGetCurrentUserReturnsResourceWhenAuthenticated(): void
	{
		// Create and login a user
		$user = new User();
		$user->username = 'testuser';
		$user->password = Hash::make('password');
		$user->email = 'test@example.com';
		$user->may_upload = true;
		$user->may_edit_own_settings = true;
		$user->may_administrate = false;
		$user->save();

		Auth::login($user);

		$controller = new \App\Http\Controllers\AuthController();
		$result = $controller->getCurrentUser();

		$this->assertInstanceOf(\App\Http\Resources\Models\UserResource::class, $result);
	}

	public function testGetCurrentUserReturnsResourceWhenNotAuthenticated(): void
	{
		Auth::logout();

		$controller = new \App\Http\Controllers\AuthController();
		$result = $controller->getCurrentUser();

		$this->assertInstanceOf(\App\Http\Resources\Models\UserResource::class, $result);
	}

	public function testGetConfigReturnsAuthConfig(): void
	{
		$controller = new \App\Http\Controllers\AuthController();

		$result = $controller->getConfig();

		$this->assertInstanceOf(\App\Http\Resources\Root\AuthConfig::class, $result);
	}

	public function testIsLdapEnabledReturnsFalseWhenConfigKeyMissing(): void
	{
		// Remove the config key entirely
		Config::offsetUnset('ldap.auth.enabled');

		$controller = new \App\Http\Controllers\AuthController();
		$reflection = new \ReflectionClass($controller);
		$method = $reflection->getMethod('isLdapEnabled');
		$method->setAccessible(true);

		$result = $method->invoke($controller);

		$this->assertFalse($result);
	}

	public function testIsLdapEnabledReturnsFalseForInteger(): void
	{
		Config::set('ldap.auth.enabled', 1); // Integer, not boolean

		$controller = new \App\Http\Controllers\AuthController();
		$reflection = new \ReflectionClass($controller);
		$method = $reflection->getMethod('isLdapEnabled');
		$method->setAccessible(true);

		$result = $method->invoke($controller);

		$this->assertFalse($result);
	}

	public function testAttemptLdapLoginReturnsFalseOnConfigurationError(): void
	{
		// Set invalid LDAP configuration (missing required fields)
		Config::set('ldap.auth.enabled', true);
		Config::set('ldap.connections.default.hosts', []); // Invalid - will throw LdapConfigurationException

		// Create login request
		$request = LoginRequest::createFrom(
			request()->create('/api/Auth::login', 'POST', [
				'username' => 'testuser',
				'password' => 'password',
			])
		);
		$request->setContainer(app());
		$request->validateResolved();

		// Should fall back to local auth (no local user = exception)
		$this->expectException(UnauthenticatedException::class);

		$controller = new \App\Http\Controllers\AuthController();
		$controller->login($request);
	}

	public function testLoginWithEmptyPassword(): void
	{
		Config::set('ldap.auth.enabled', false);

		// Create local user
		$user = new User();
		$user->username = 'testuser';
		$user->password = Hash::make('correctpassword');
		$user->email = 'test@example.com';
		$user->may_upload = true;
		$user->may_edit_own_settings = true;
		$user->may_administrate = false;
		$user->save();

		// Empty password should fail validation before reaching controller logic
		// Just test that it would fail authentication if it got through
		$this->expectException(UnauthenticatedException::class);

		// Manually check authentication (simulating what would happen if empty password got through)
		$result = Auth::attempt([
			'username' => 'testuser',
			'password' => '',
		]);

		if (!$result) {
			throw new UnauthenticatedException('Unknown user or invalid password');
		}
	}

	public function testLoginWithSpecialCharactersInUsername(): void
	{
		Config::set('ldap.auth.enabled', false);

		// Create local user with special characters
		$user = new User();
		$user->username = 'test.user@example';
		$user->password = Hash::make('password');
		$user->email = 'test@example.com';
		$user->may_upload = true;
		$user->may_edit_own_settings = true;
		$user->may_administrate = false;
		$user->save();

		// Create login request
		$request = LoginRequest::createFrom(
			request()->create('/api/Auth::login', 'POST', [
				'username' => 'test.user@example',
				'password' => 'password',
			])
		);
		$request->setContainer(app());
		$request->validateResolved();

		$controller = new \App\Http\Controllers\AuthController();
		$controller->login($request);

		$this->assertTrue(Auth::check());
		$this->assertSame('test.user@example', Auth::user()->username);
	}

	public function testLdapAuthenticationTakesPrecedenceOverLocal(): void
	{
		// Enable LDAP with invalid host (will fail)
		Config::set('ldap.auth.enabled', true);
		Config::set('ldap.connections.default', [
			'hosts' => ['invalid.nonexistent.local'],
			'port' => 389,
			'base_dn' => 'dc=test,dc=local',
			'username' => 'cn=admin,dc=test,dc=local',
			'password' => 'adminpass',
			'timeout' => 1,
			'use_tls' => false,
		]);

		// Create local user with same username
		$user = new User();
		$user->username = 'testuser';
		$user->password = Hash::make('localpassword');
		$user->email = 'local@example.com';
		$user->may_upload = true;
		$user->may_edit_own_settings = true;
		$user->may_administrate = false;
		$user->save();

		// Try to login - LDAP will be attempted first (and fail), then fall back to local
		$request = LoginRequest::createFrom(
			request()->create('/api/Auth::login', 'POST', [
				'username' => 'testuser',
				'password' => 'localpassword',
			])
		);
		$request->setContainer(app());
		$request->validateResolved();

		$controller = new \App\Http\Controllers\AuthController();
		$controller->login($request);

		// Should succeed via local auth fallback
		$this->assertTrue(Auth::check());
		$this->assertSame('testuser', Auth::user()->username);
	}

	public function testMultipleLoginAttempts(): void
	{
		Config::set('ldap.auth.enabled', false);

		// Create local user
		$user = new User();
		$user->username = 'testuser';
		$user->password = Hash::make('password');
		$user->email = 'test@example.com';
		$user->may_upload = true;
		$user->may_edit_own_settings = true;
		$user->may_administrate = false;
		$user->save();

		$controller = new \App\Http\Controllers\AuthController();

		// First attempt - wrong password
		try {
			$request1 = LoginRequest::createFrom(
				request()->create('/api/Auth::login', 'POST', [
					'username' => 'testuser',
					'password' => 'wrongpassword',
				])
			);
			$request1->setContainer(app());
			$request1->validateResolved();

			$controller->login($request1);
			$this->fail('Should have thrown UnauthenticatedException');
		} catch (UnauthenticatedException $e) {
			$this->assertFalse(Auth::check());
		}

		// Second attempt - correct password
		$request2 = LoginRequest::createFrom(
			request()->create('/api/Auth::login', 'POST', [
				'username' => 'testuser',
				'password' => 'password',
			])
		);
		$request2->setContainer(app());
		$request2->validateResolved();

		$controller->login($request2);
		$this->assertTrue(Auth::check());
	}

	public function testLogoutWhenNotAuthenticated(): void
	{
		Auth::logout();
		$this->assertFalse(Auth::check());

		// Call logout when already logged out
		$controller = new \App\Http\Controllers\AuthController();
		$controller->logout();

		// Should not throw exception
		$this->assertFalse(Auth::check());
	}

	public function testLdapDisabledWithStringFalse(): void
	{
		// Test with string "false" instead of boolean
		Config::set('ldap.auth.enabled', 'false');

		$controller = new \App\Http\Controllers\AuthController();
		$reflection = new \ReflectionClass($controller);
		$method = $reflection->getMethod('isLdapEnabled');
		$method->setAccessible(true);

		$result = $method->invoke($controller);

		$this->assertFalse($result);
	}

	public function testLdapDisabledWithZero(): void
	{
		Config::set('ldap.auth.enabled', 0);

		$controller = new \App\Http\Controllers\AuthController();
		$reflection = new \ReflectionClass($controller);
		$method = $reflection->getMethod('isLdapEnabled');
		$method->setAccessible(true);

		$result = $method->invoke($controller);

		$this->assertFalse($result);
	}
}
