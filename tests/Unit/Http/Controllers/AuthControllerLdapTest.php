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
}
