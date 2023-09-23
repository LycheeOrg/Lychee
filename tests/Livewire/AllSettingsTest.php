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

use App\Livewire\Components\Pages\AllSettings;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class AllSettingsTest extends BaseLivewireTest
{
	public function testAllSettingsLoggedOut(): void
	{
		Livewire::test(AllSettings::class)
			->assertForbidden();
	}

	public function testAllSettingsLoggedIn(): void
	{
		Livewire::actingAs($this->admin)->test(AllSettings::class)
			->assertViewIs('livewire.pages.all-settings');
	}
}
