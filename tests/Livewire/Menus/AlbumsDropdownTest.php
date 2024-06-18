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

namespace Tests\Livewire\Menus;

use App\Contracts\Livewire\Params;
use App\Livewire\Components\Menus\AlbumsDropdown;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class AlbumsDropdownTest extends BaseLivewireTest
{
	public function testMenu(): void
	{
		Livewire::test(
			AlbumsDropdown::class,
			['params' => [Params::PARENT_ID => null, Params::ALBUM_IDS => [$this->album1->id, $this->album2->id]]]
		)
			->assertViewIs('livewire.context-menus.albums-dropdown')
			->assertStatus(200);
	}

	public function testRename(): void
	{
		Livewire::test(
			AlbumsDropdown::class,
			['params' => [Params::PARENT_ID => null, Params::ALBUM_IDS => [$this->album1->id, $this->album2->id]]]
		)
			->call('renameAll')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.album.rename');
	}

	public function testMerge(): void
	{
		Livewire::test(
			AlbumsDropdown::class,
			['params' => [Params::PARENT_ID => null, Params::ALBUM_IDS => [$this->album1->id, $this->album2->id]]]
		)
			->call('mergeAll')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.album.merge');
	}

	public function testDelete(): void
	{
		Livewire::test(
			AlbumsDropdown::class,
			['params' => [Params::PARENT_ID => null, Params::ALBUM_IDS => [$this->album1->id, $this->album2->id]]]
		)
			->call('deleteAll')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.album.delete');
	}

	public function testMove(): void
	{
		Livewire::test(
			AlbumsDropdown::class,
			['params' => [Params::PARENT_ID => null, Params::ALBUM_IDS => [$this->album1->id, $this->album2->id]]]
		)
			->call('moveAll')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.album.move');
	}

	public function testDownload(): void
	{
		Livewire::test(
			AlbumsDropdown::class,
			['params' => [Params::PARENT_ID => null, Params::ALBUM_IDS => [$this->album1->id, $this->album2->id]]]
		)
			->call('downloadAll')
			->assertDispatched('closeContextMenu')
			->assertRedirect(route('download') . '?albumIDs=' . $this->album1->id . ',' . $this->album2->id);
	}
}
