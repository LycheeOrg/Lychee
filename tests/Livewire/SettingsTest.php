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

use App\Livewire\Components\Pages\Settings;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class SettingsTest extends BaseLivewireTest
{
	public function testSettingsLoggedOut(): void
	{
		Livewire::test(Settings::class)
			->assertForbidden();
	}

	public function testSettingsLoggedIn(): void
	{
		Livewire::actingAs($this->admin)->test(Settings::class)
			->assertViewIs('livewire.pages.settings');
	}
}
