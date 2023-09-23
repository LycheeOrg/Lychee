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

use App\Livewire\Components\Pages\Landing;
use App\Models\Configs;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class LandingTest extends BaseLivewireTest
{
	private $landing_on_off;

	protected function setUp(): void
    {
        parent::setUp();
		$this->landing_on_off = Configs::getValue('landing_page_enable');
		Configs::set('landing_page_enable', 1);
	}

	protected function tearDown(): void
	{
		Configs::set('landing_page_enable', $this->landing_on_off);
		parent::tearDown();
	}

	public function testLandingPage(): void
	{
		$title = Configs::getValueAsString('landing_title');
		$subtitle = Configs::getValueAsString('landing_subtitle');
		$background = Configs::getValueAsString('landing_background');
		Livewire::test(Landing::class)
			->assertViewIs('livewire.pages.landing')
			->assertSee($title)
			->assertSee($subtitle)
			->assertSee($background);
	}

}
