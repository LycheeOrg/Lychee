<?php

declare(strict_types=1);

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Livewire\Pages;

use App\Models\Configs;
use Tests\Livewire\Base\BaseLivewireTest;

class IndexTest extends BaseLivewireTest
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
		$this->assertRedirect($response);
	}

	public function testLandingPage(): void
	{
		$landing_on_off = Configs::getValue('landing_page_enable');
		Configs::set('landing_page_enable', 1);

		$response = $this->get(route('livewire-index'));
		$this->assertRedirect($response);
		$response->assertRedirect(route('landing'));

		$response = $this->get(route('landing'));
		$this->assertOk($response);

		$response = $this->get(route('livewire-gallery'));
		$this->assertOk($response);

		Configs::set('landing_page_enable', $landing_on_off);
	}

	public function testGalleryPage(): void
	{
		$landing_on_off = Configs::getValue('landing_page_enable');
		Configs::set('landing_page_enable', 0);

		$response = $this->get(route('livewire-index'));
		$this->assertRedirect($response);
		$response->assertRedirect(route('livewire-gallery'));

		$response = $this->get(route('livewire-gallery'));
		$this->assertOk($response);

		$response = $this->get(route('landing'));
		$this->assertRedirect($response);

		Configs::set('landing_page_enable', $landing_on_off);
	}
}
