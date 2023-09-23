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

use App\Livewire\Components\Menus\PhotosDropdown;
use App\Models\Album;
use App\Models\Photo;
use Livewire\Livewire;
use Tests\Feature\Traits\RequiresEmptyAlbums;
use Tests\Feature\Traits\RequiresEmptyPhotos;
use Tests\Livewire\Base\BaseLivewireTest;

class PhotosDropdownTest extends BaseLivewireTest
{
	use RequiresEmptyAlbums;
	use RequiresEmptyPhotos;

	private Album $album;
	private Photo $photo1;
	private Photo $photo2;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyPhotos();
		$this->setUpRequiresEmptyAlbums();

		$this->photo1 = Photo::factory()->create();
		$this->photo2 = Photo::factory()->create();

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
		Livewire::test(PhotosDropdown::class,
			['params' => ['albumID' => $this->album->id, 'photoIDs' => [$this->photo1->id, $this->photo2->id]]])
			->assertViewIs('livewire.context-menus.photos-dropdown')
			->assertStatus(200);
	}

	public function testRename(): void
	{
		Livewire::test(PhotosDropdown::class,
			['params' => ['albumID' => $this->album->id, 'photoIDs' => [$this->photo1->id, $this->photo2->id]]])
			->call('renameAll')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.photo.rename');
	}

	public function testStarGuest(): void
	{
		Livewire::test(PhotosDropdown::class,
			['params' => ['albumID' => $this->album->id, 'photoIDs' => [$this->photo1->id, $this->photo2->id]]])
			->call('starAll')
			->assertStatus(403);
	}

	public function testStar(): void
	{
		Livewire::actingAs($this->admin)->test(PhotosDropdown::class,
			['params' => ['albumID' => $this->album->id, 'photoIDs' => [$this->photo1->id, $this->photo2->id]]])
			->call('starAll')
			->assertDispatched('closeContextMenu')
			->assertDispatched('reloadPage');
	}

	public function testUnstarGuest(): void
	{
		Livewire::test(PhotosDropdown::class,
			['params' => ['albumID' => $this->album->id, 'photoIDs' => [$this->photo1->id, $this->photo2->id]]])
			->call('unstarAll')
			->assertStatus(403);
	}

	public function testUnstar(): void
	{
		Livewire::actingAs($this->admin)->test(PhotosDropdown::class,
			['params' => ['albumID' => $this->album->id, 'photoIDs' => [$this->photo1->id, $this->photo2->id]]])
			->call('unstarAll')
			->assertDispatched('closeContextMenu')
			->assertDispatched('reloadPage');
	}

	public function testTag(): void
	{
		Livewire::test(PhotosDropdown::class,
			['params' => ['albumID' => $this->album->id, 'photoIDs' => [$this->photo1->id, $this->photo2->id]]])
			->call('tagAll')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.photo.tag');
	}

	public function testDelete(): void
	{
		Livewire::test(PhotosDropdown::class,
			['params' => ['albumID' => $this->album->id, 'photoIDs' => [$this->photo1->id, $this->photo2->id]]])
			->call('deleteAll')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.photo.delete');
	}

	public function testMove(): void
	{
		Livewire::test(PhotosDropdown::class,
			['params' => ['albumID' => $this->album->id, 'photoIDs' => [$this->photo1->id, $this->photo2->id]]])
			->call('moveAll')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.photo.move');
	}

	public function testDownload(): void
	{
		Livewire::test(PhotosDropdown::class,
			['params' => ['albumID' => $this->album->id, 'photoIDs' => [$this->photo1->id, $this->photo2->id]]])
			->call('downloadAll')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.photo.download');
		// ->assertRedirect(route('photo_download', ['kind' => DownloadVariantType::ORIGINAL->value, 'photoIDs' => $this->photo->id]));
	}
}
