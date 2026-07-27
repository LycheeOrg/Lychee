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

namespace Tests\Install;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

class AdminSetupTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testRedirectsToLegacyInstallWhenNuxtUiInactive(): void
	{
		config(['features.nuxt_ui' => false]);

		$response = $this->get('/');
		self::assertEquals(307, $response->getStatusCode());
		$response->assertRedirect('/install/admin');
	}

	public function testRedirectsToV8SetupWhenNuxtUiActive(): void
	{
		config(['features.nuxt_ui' => true]);

		$response = $this->get('/');
		self::assertEquals(307, $response->getStatusCode());
		$response->assertRedirect('/setup-admin');

		config(['features.nuxt_ui' => false]);
	}

	public function testSetupAdminRouteServesVueAppWhenNoAdmin(): void
	{
		$this->withoutVite();
		$response = $this->get('/setup-admin');
		$this->assertOk($response);
		$response->assertViewIs('vueapp');
	}

	public function testSetupAdminRouteGuardedOnceAdminExists(): void
	{
		User::factory()->may_administrate()->create();

		$response = $this->get('/setup-admin');
		self::assertNotEquals(200, $response->getStatusCode());
	}
}
