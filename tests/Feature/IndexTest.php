<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature;

use App\Facades\AccessControl;
use App\Models\Configs;
use Tests\TestCase;

class IndexTest extends TestCase
{
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
		$response->assertOk();

		$response = $this->postJson('/api/Albums::get');
		$response->assertOk();
	}

	/**
	 * More tests.
	 *
	 * @return void
	 */
	public function testPhpInfo(): void
	{
		AccessControl::logout();
		// we don't want a non admin to access this
		$response = $this->get('/phpinfo');
		$response->assertForbidden();
	}

	public function testLandingPage(): void
	{
		$landing_on_off = Configs::get_value('landing_page_enable', '0');
		Configs::set('landing_page_enable', 1);

		$response = $this->get('/');
		$response->assertOk();
		$response->assertViewIs('landing');

		$response = $this->get('/gallery');
		$response->assertOk();
		$response->assertViewIs('gallery');

		Configs::set('landing_page_enable', $landing_on_off);
	}
}
