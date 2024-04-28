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
use App\Livewire\Components\Menus\PhotoDropdown;
use App\Models\Photo;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class PhotoDropdownTest extends BaseLivewireTest
{
	public function testMenu(): void
	{
		Livewire::test(PhotoDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->photo1->id]])
			->assertViewIs('livewire.context-menus.photo-dropdown')
			->assertStatus(200);
	}

	public function testRename(): void
	{
		Livewire::test(PhotoDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->photo1->id]])
			->call('rename')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.photo.rename');
	}

	public function testStarGuest(): void
	{
		Livewire::test(PhotoDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->photo1->id]])
			->call('star')
			->assertStatus(403);
	}

	public function testStar(): void
	{
		Livewire::actingAs($this->admin)->test(PhotoDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->photo1->id]])
			->call('star')
			->assertDispatched('closeContextMenu')
			->assertDispatched('reloadPage');
	}

	public function testUnstarGuest(): void
	{
		Livewire::test(PhotoDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->photo1->id]])
			->call('unstar')
			->assertStatus(403);
	}

	public function testUnstar(): void
	{
		Livewire::actingAs($this->admin)->test(PhotoDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->photo1->id]])
			->call('unstar')
			->assertDispatched('closeContextMenu')
			->assertDispatched('reloadPage');
	}

	public function testSetAsCoverGuest(): void
	{
		Livewire::test(PhotoDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->photo1->id]])
			->call('setAsCover')
			->assertStatus(403);
	}

	public function testSetAsCover(): void
	{
		Livewire::actingAs($this->admin)->test(PhotoDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->photo1->id]])
			->call('setAsCover')
			->assertDispatched('closeContextMenu')
			->assertDispatched('reloadPage');
	}

	public function testSetAsHeaderGuest(): void
	{
		Livewire::test(PhotoDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->photo1->id]])
			->call('setAsHeader')
			->assertStatus(403);
	}

	public function testSetAsHeader(): void
	{
		Livewire::actingAs($this->admin)->test(PhotoDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->photo1->id]])
			->call('setAsHeader')
			->assertDispatched('closeContextMenu')
			->assertDispatched('reloadPage');
	}

	public function testTag(): void
	{
		Livewire::test(PhotoDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->photo1->id]])
			->call('tag')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.photo.tag');
	}

	public function testDelete(): void
	{
		Livewire::test(PhotoDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->photo1->id]])
			->call('delete')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.photo.delete');
	}

	public function testMove(): void
	{
		Livewire::test(PhotoDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->photo1->id]])
			->call('move')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.photo.move');
	}

	public function testCopyTo(): void
	{
		Livewire::test(PhotoDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->photo1->id]])
			->call('copyTo')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.photo.copy-to');
	}

	public function testDownload(): void
	{
		Livewire::test(PhotoDropdown::class,
			['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->photo1->id]])
			->call('download')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.photo.download');
		// ->assertRedirect(route('photo_download', ['kind' => DownloadVariantType::ORIGINAL->value, 'photoIDs' => $this->photo->id]));
	}
}
