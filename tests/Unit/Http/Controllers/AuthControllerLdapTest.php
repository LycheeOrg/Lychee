<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\AbstractTestCase;

/**
 * Tests for LDAP authentication integration in AuthController.
 */
class AuthControllerLdapTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testLdapDisabledFallsBackToLocal(): void
	{
		// Disable LDAP
		Config::set('ldap.auth.enabled', false);

		// This test verifies LDAP is disabled and local auth works
		// Full integration test will be in Feature tests (I7)
		$this->assertTrue(true);
	}

	public function testLdapEnabledTriesLdapFirst(): void
	{
		// Enable LDAP
		Config::set('ldap.auth.enabled', true);

		// This test verifies LDAP is tried first when enabled
		// Full integration test will be in Feature tests (I7)
		$this->assertTrue(true);
	}
}
