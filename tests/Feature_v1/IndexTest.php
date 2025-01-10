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

namespace Tests\Feature_v1;

use App\Models\Configs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\AbstractTestCase;

class IndexTest extends AbstractTestCase
{
	private bool $configVueJs;

	public function before(): void
	{
		$this->configVueJs = config('feature.vuejs');
		config(['feature.vuejs' => false]);
	}

	public function after(): void
	{
		config(['feature.vuejs' => $this->configVueJs]);
	}

	/**
	 * Testing the Login interface.
	 *
	 * @return void
	 */
	public function testHome(): void
	{
		/**
		 * check if we can actually get a nice answer.
		 */
		$response = $this->get('/');
		if (config('feature.livewire') === true) {
			$this->assertRedirect($response);
			$response->assertRedirect(route('livewire-gallery'));
		} else {
			$this->assertOk($response);
		}

		$response = $this->postJson('/api/Albums::get');
		$this->assertOk($response);
	}

	/**
	 * More tests.
	 *
	 * @return void
	 */
	public function testPhpInfo(): void
	{
		Auth::logout();
		Session::flush();
		// we don't want a non admin to access this
		$response = $this->get('/phpinfo');
		$this->assertForbidden($response);
	}

	public function testLandingPage(): void
	{
		$landing_on_off = Configs::getValue('landing_page_enable');
		Configs::set('landing_page_enable', 1);

		$response = $this->get('/');
		if (config('feature.livewire') === true) {
			$this->assertRedirect($response);
			$response->assertRedirect(route('landing'));
		} else {
			$this->assertOk($response);
			$response->assertViewIs('landing');
		}

		$response = $this->get('/gallery');
		$this->assertOk($response);

		Configs::set('landing_page_enable', $landing_on_off);
	}
}
