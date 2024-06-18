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

use App\Livewire\Components\Pages\Map;
use App\Models\Configs;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class MapTest extends BaseLivewireTest
{
	public function testMapLoggedOutForbidden(): void
	{
		Livewire::test(Map::class)
			->assertForbidden();
	}

	public function testMapLoggedInDisabled(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(Map::class)
			->assertForbidden();
	}

	public function testMapLoggedInEnabled(): void
	{
		Configs::set('map_display', true);
		Livewire::actingAs($this->userMayUpload1)->test(Map::class)
			->assertOk()
			->assertViewIs('livewire.pages.gallery.map');

		Configs::set('map_display', false);
	}

	public function testMapLoggedOutEnabled(): void
	{
		Configs::set('map_display', true);
		Configs::set('map_display_public', true);

		Livewire::test(Map::class)
			->assertOk()
			->assertViewIs('livewire.pages.gallery.map');

		Configs::set('map_display', false);
		Configs::set('map_display_public', false);
	}

	public function testMapLoggedOutAlbum(): void
	{
		Livewire::test(Map::class, ['albumId' => $this->album1->id])
			->assertForbidden();

		Configs::set('map_display', true);
		Livewire::test(Map::class, ['albumId' => $this->album1->id])
			->assertForbidden();

		Configs::set('map_display_public', true);
		Livewire::test(Map::class, ['albumId' => $this->album1->id])
			->assertForbidden();

		Configs::set('map_display', false);
		Configs::set('map_display_public', false);
	}

	public function testMapLoggedInAlbum(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(Map::class)
			->assertForbidden();

		Configs::set('map_display', true);
		Livewire::actingAs($this->userMayUpload1)->test(Map::class)
			->assertOk()
			->assertViewIs('livewire.pages.gallery.map');
		Configs::set('map_display', false);
	}
}
