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

use App\Livewire\Components\Menus\PhotoDropdown;
use App\Models\Album;
use App\Models\Photo;
use Livewire\Livewire;
use Tests\Feature\Traits\RequiresEmptyAlbums;
use Tests\Feature\Traits\RequiresEmptyPhotos;
use Tests\Livewire\Base\BaseLivewireTest;

class PhotoDropdownTest extends BaseLivewireTest
{
	use RequiresEmptyAlbums;
	use RequiresEmptyPhotos;

	private Album $album;
	private Photo $photo;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyPhotos();
		$this->setUpRequiresEmptyAlbums();

		$this->photo = Photo::factory()->create();

		$this->album = new Album();
		$this->album->title = fake()->title;
		$this->album->owner_id = $this->admin->id;
		$this->album->makeRoot();
		$this->album->save();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyPhotos();
		$this->tearDownRequiresEmptyAlbums();
		parent::tearDown();
	}

	public function testMenu(): void
	{
		Livewire::test(PhotoDropdown::class,
			['params' => ['albumID' => $this->album->id, 'photoID' => $this->photo->id]])
			->assertViewIs('livewire.context-menus.photo-dropdown')
			->assertStatus(200);
	}

	public function testRename(): void
	{
		Livewire::test(PhotoDropdown::class,
			['params' => ['albumID' => $this->album->id, 'photoID' => $this->photo->id]])
			->call('rename')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.photo.rename');
	}

	public function testStarGuest(): void
	{
		Livewire::test(PhotoDropdown::class,
			['params' => ['albumID' => $this->album->id, 'photoID' => $this->photo->id]])
			->call('star')
			->assertStatus(403);
	}

	public function testStar(): void
	{
		Livewire::actingAs($this->admin)->test(PhotoDropdown::class,
			['params' => ['albumID' => $this->album->id, 'photoID' => $this->photo->id]])
			->call('star')
			->assertDispatched('closeContextMenu')
			->assertDispatched('reloadPage');
	}

	public function testUnstarGuest(): void
	{
		Livewire::test(PhotoDropdown::class,
			['params' => ['albumID' => $this->album->id, 'photoID' => $this->photo->id]])
			->call('unstar')
			->assertStatus(403);
	}

	public function testUnstar(): void
	{
		Livewire::actingAs($this->admin)->test(PhotoDropdown::class,
			['params' => ['albumID' => $this->album->id, 'photoID' => $this->photo->id]])
			->call('unstar')
			->assertDispatched('closeContextMenu')
			->assertDispatched('reloadPage');
	}

	public function testSetAsCoverGuest(): void
	{
		Livewire::test(PhotoDropdown::class,
			['params' => ['albumID' => $this->album->id, 'photoID' => $this->photo->id]])
			->call('setAsCover')
			->assertStatus(403);
	}

	public function testSetAsCover(): void
	{
		Livewire::actingAs($this->admin)->test(PhotoDropdown::class,
			['params' => ['albumID' => $this->album->id, 'photoID' => $this->photo->id]])
			->call('setAsCover')
			->assertDispatched('closeContextMenu')
			->assertDispatched('reloadPage');
	}

	public function testTag(): void
	{
		Livewire::test(PhotoDropdown::class,
			['params' => ['albumID' => $this->album->id, 'photoID' => $this->photo->id]])
			->call('tag')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.photo.tag');
	}

	public function testDelete(): void
	{
		Livewire::test(PhotoDropdown::class,
			['params' => ['albumID' => $this->album->id, 'photoID' => $this->photo->id]])
			->call('delete')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.photo.delete');
	}

	public function testMove(): void
	{
		Livewire::test(PhotoDropdown::class,
			['params' => ['albumID' => $this->album->id, 'photoID' => $this->photo->id]])
			->call('move')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.photo.move');
	}

	public function testCopyTo(): void
	{
		Livewire::test(PhotoDropdown::class,
			['params' => ['albumID' => $this->album->id, 'photoID' => $this->photo->id]])
			->call('copyTo')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.photo.copy-to');
	}

	public function testDownload(): void
	{
		Livewire::test(PhotoDropdown::class,
			['params' => ['albumID' => $this->album->id, 'photoID' => $this->photo->id]])
			->call('download')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.photo.download');
		// ->assertRedirect(route('photo_download', ['kind' => DownloadVariantType::ORIGINAL->value, 'photoIDs' => $this->photo->id]));
	}
}
