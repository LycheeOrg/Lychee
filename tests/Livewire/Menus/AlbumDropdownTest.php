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

namespace Tests\Livewire\Menus;

use App\Livewire\Components\Menus\AlbumDropdown;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class AlbumDropdownTest extends BaseLivewireTest
{
	public function testMenu(): void
	{
		Livewire::test(
			AlbumDropdown::class,
			['params' => ['parentID' => null, 'albumID' => $this->album1->id]]
		)
			->assertViewIs('livewire.context-menus.album-dropdown')
			->assertStatus(200);
	}

	public function testRename(): void
	{
		Livewire::test(
			AlbumDropdown::class,
			['params' => ['parentID' => null, 'albumID' => $this->album1->id]]
		)
			->call('rename')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.album.rename');
	}

	public function testMerge(): void
	{
		Livewire::test(
			AlbumDropdown::class,
			['params' => ['parentID' => null, 'albumID' => $this->album1->id]]
		)
			->call('merge')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.album.merge');
	}

	public function testDelete(): void
	{
		Livewire::test(
			AlbumDropdown::class,
			['params' => ['parentID' => null, 'albumID' => $this->album1->id]]
		)
			->call('delete')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.album.delete');
	}

	public function testMove(): void
	{
		Livewire::test(
			AlbumDropdown::class,
			['params' => ['parentID' => null, 'albumID' => $this->album1->id]]
		)
			->call('move')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.album.move');
	}

	public function testDownload(): void
	{
		Livewire::test(
			AlbumDropdown::class,
			['params' => ['parentID' => null, 'albumID' => $this->album1->id]]
		)
			->call('download')
			->assertDispatched('closeContextMenu')
			->assertRedirect(route('download', ['albumIDs' => $this->album1->id]));
	}
}
