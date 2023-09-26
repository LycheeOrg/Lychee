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

use App\Enum\AlbumLayoutType;
use App\Enum\SmartAlbumType;
use App\Livewire\Components\Pages\Gallery\Album;
use App\Livewire\Components\Pages\Gallery\Albums;
use App\Models\Configs;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class AlbumTest extends BaseLivewireTest
{
	public function testUrlPageLogout(): void
	{
		// In this specific case we allow to find the album but we do not display it (tested later)
		$response = $this->get(route('livewire-gallery-album', ['albumId' => $this->album1->id]));
		$this->assertOk($response);

		$response = $this->get(route('livewire-gallery-album', ['albumId' => '1234567890']));
		$this->assertNotFound($response);
	}

	public function testPageLogout(): void
	{
		Livewire::test(Album::class, ['albumId' => $this->album1->id])
			->assertViewIs('livewire.pages.gallery.album')
			->assertSee($this->album1->id)
			->assertOk()
			->assertSet('flags.is_accessible', false)
			->dispatch('reloadPage');
	}

	public function testPageUserNoAccess(): void
	{
		Livewire::actingAs($this->userMayUpload2)->test(Album::class, ['albumId' => $this->album1->id])
			->assertRedirect(route('livewire-gallery'));

		Livewire::actingAs($this->userNoUpload)->test(Album::class, ['albumId' => $this->album1->id])
			->assertRedirect(route('livewire-gallery'));
	}

	public function testPageLoginAndBack(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(Album::class, ['albumId' => $this->album1->id])
			->assertViewIs('livewire.pages.gallery.album')
			->assertSee($this->album1->id)
			->assertOk()
			->assertSet('flags.is_accessible', true)
			->call('silentUpdate')
			->assertOk()
			->call('back')
			->assertRedirect(Albums::class);
	}

	public function testPageLoginAndBackFromSubAlbum(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(Album::class, ['albumId' => $this->subAlbum1->id])
			->assertViewIs('livewire.pages.gallery.album')
			->assertSee($this->subAlbum1->id)
			->assertOk()
			->assertSet('flags.is_accessible', true)
			->call('silentUpdate')
			->assertOk()
			->call('back')
			->assertRedirect(route('livewire-gallery-album', ['albumId' => $this->album1->id]));
	}

	public function testMenus(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(Album::class, ['albumId' => $this->album1->id])
			->assertViewIs('livewire.pages.gallery.album')
			->assertSee($this->album1->id)
			->assertSee($this->subAlbum1->id)
			->assertSee($this->photo1->id)
			->assertSee($this->subPhoto1->size_variants->getThumb()->url)
			->assertOk()
			->call('loadAlbum', 1900)
			->assertOk()
			->call('openContextMenu')
			->assertOk()
			->call('openAlbumDropdown', 0, 0, $this->subAlbum1->id)
			->assertOk()
			->call('openAlbumsDropdown', 0, 0, [$this->subAlbum1->id])
			->assertOk()
			->call('openPhotoDropdown', 0, 0, $this->photo1->id)
			->assertOk()
			->call('openPhotosDropdown', 0, 0, [$this->photo1->id])
			->assertOk();
	}

	public function testActions(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(Album::class, ['albumId' => $this->album1->id])
			->assertViewIs('livewire.pages.gallery.album')
			->assertSee($this->album1->id)
			->assertSee($this->subAlbum1->id)
			->assertSee($this->photo1->id)
			->assertSee($this->subPhoto1->size_variants->getThumb()->url)
			->assertOk()
			->call('loadAlbum', 1900)
			->assertOk()
			->call('setStar', [$this->photo1->id])
			->assertOk()
			->call('unsetStar', [$this->photo1->id])
			->assertOk()
			->call('setCover', $this->photo1->id)
			->assertDispatched('notify', self::notifySuccess())
			->assertOk();
	}

	public function testActionsUnsorted(): void
	{
		Configs::set('layout', AlbumLayoutType::SQUARE);

		Livewire::actingAs($this->userMayUpload1)->test(Album::class, ['albumId' => SmartAlbumType::UNSORTED->value])
			->assertViewIs('livewire.pages.gallery.album')
			->assertSee($this->photoUnsorted->id)
			->assertOk()
			->call('loadAlbum', 1900)
			->assertOk()
			->call('setStar', [$this->photoUnsorted->id])
			->assertOk()
			->call('unsetStar', [$this->photoUnsorted->id])
			->assertOk()
			->call('setCover', $this->photoUnsorted->id)
			->assertNotDispatched('notify')
			->assertOk();

		Configs::set('layout', AlbumLayoutType::JUSTIFIED);
	}
}
