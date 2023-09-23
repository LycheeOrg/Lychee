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

namespace Tests\Livewire\Modals;

use App\Livewire\Components\Modals\About;
use App\Metadata\Versions\InstalledVersion;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class AboutTest extends BaseLivewireTest
{
	public function testGallery(): void
	{
		Livewire::test(About::class)
			->assertViewIs('livewire.modals.about')
			->assertSet('is_new_release_available', false)
			->assertSet('is_git_update_available', false)
			->assertSet('version', resolve(InstalledVersion::class)->getVersion()->toString());
	}
}
