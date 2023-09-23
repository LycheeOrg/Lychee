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

namespace Tests\Livewire\Pages;

use App\Livewire\Components\Pages\Gallery\Albums;
use App\Models\Configs;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class GalleryTest extends BaseLivewireTest
{
	public function testGallery(): void
	{
		$title = Configs::getValueAsString('site_title');

		Livewire::test(Albums::class)
			->assertViewIs('livewire.pages.gallery.albums')
			->assertSee($title)
			->dispatch('reloadPage')
			->assertStatus(200);
	}
}
