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

namespace Tests\Livewire;

use App\Models\Configs;
use Tests\AbstractTestCase;

class IndexTest extends AbstractTestCase
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
		$response = $this->get(route('livewire-index'));
		$this->assertOk($response);
	}

	public function testLandingPage(): void
	{
		$landing_on_off = Configs::getValue('landing_page_enable');
		Configs::set('landing_page_enable', 1);

		$response = $this->get('/');
		$this->assertOk($response);
		$response->assertViewIs('landing');

		$response = $this->get('/gallery');
		$this->assertOk($response);

		Configs::set('landing_page_enable', $landing_on_off);
	}
}
