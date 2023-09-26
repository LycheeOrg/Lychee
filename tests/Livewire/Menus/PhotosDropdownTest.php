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

use App\Contracts\Livewire\Params;
use App\Livewire\Components\Menus\PhotosDropdown;
use App\Models\Photo;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class PhotosDropdownTest extends BaseLivewireTest
{
	public function testMenu(): void
	{
		Livewire::test(PhotosDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_IDS => [$this->photo1->id, $this->photo1b->id]]])
			->assertViewIs('livewire.context-menus.photos-dropdown')
			->assertStatus(200);
	}

	public function testRename(): void
	{
		Livewire::test(PhotosDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_IDS => [$this->photo1->id, $this->photo1b->id]]])
			->call('renameAll')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.photo.rename');
	}

	public function testStarGuest(): void
	{
		Livewire::test(PhotosDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_IDS => [$this->photo1->id, $this->photo1b->id]]])
			->call('starAll')
			->assertStatus(403);
	}

	public function testStar(): void
	{
		Livewire::actingAs($this->admin)->test(PhotosDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_IDS => [$this->photo1->id, $this->photo1b->id]]])
			->call('starAll')
			->assertDispatched('closeContextMenu')
			->assertDispatched('reloadPage');
	}

	public function testUnstarGuest(): void
	{
		Livewire::test(PhotosDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_IDS => [$this->photo1->id, $this->photo1b->id]]])
			->call('unstarAll')
			->assertStatus(403);
	}

	public function testUnstar(): void
	{
		Livewire::actingAs($this->admin)->test(PhotosDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_IDS => [$this->photo1->id, $this->photo1b->id]]])
			->call('unstarAll')
			->assertDispatched('closeContextMenu')
			->assertDispatched('reloadPage');
	}

	public function testTag(): void
	{
		Livewire::test(PhotosDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_IDS => [$this->photo1->id, $this->photo1b->id]]])
			->call('tagAll')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.photo.tag');
	}

	public function testDelete(): void
	{
		Livewire::test(PhotosDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_IDS => [$this->photo1->id, $this->photo1b->id]]])
			->call('deleteAll')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.photo.delete');
	}

	public function testMove(): void
	{
		Livewire::test(PhotosDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_IDS => [$this->photo1->id, $this->photo1b->id]]])
			->call('moveAll')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.photo.move');
	}

	public function testCopyTo(): void
	{
		Livewire::test(PhotosDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_IDS => [$this->photo1->id, $this->photo1b->id]]])
			->call('copyAllTo')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.photo.copy-to');
	}

	public function testDownload(): void
	{
		Livewire::test(PhotosDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_IDS => [$this->photo1->id, $this->photo1b->id]]])
			->call('downloadAll')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.photo.download');
		// ->assertRedirect(route('photo_download', ['kind' => DownloadVariantType::ORIGINAL->value, Params::PHOTO_IDS => $this->photo->id]));
	}
}
