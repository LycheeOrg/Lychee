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

use App\Enum\SmartAlbumType;
use App\Livewire\Components\Pages\Gallery\Photo;
use App\Models\Album as ModelsAlbum;
use App\Models\Photo as ModelsPhoto;
use App\Models\SizeVariant;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\Traits\RequiresEmptyAlbums;
use Tests\Feature\Traits\RequiresEmptyPhotos;
use Tests\Feature\Traits\RequiresEmptyUsers;
use Tests\Livewire\Base\BaseLivewireTest;
use Tests\Livewire\Traits\CreateAlbum;

class PhotoTest extends BaseLivewireTest
{
	use RequiresEmptyAlbums;
	use RequiresEmptyPhotos;
	use RequiresEmptyUsers;
	use CreateAlbum;

	private ModelsAlbum $album;
	private ModelsPhoto $photo;
	private User $user;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyAlbums();
		$this->setUpRequiresEmptyPhotos();

		$this->album = $this->createAlbum();
		$this->photo = ModelsPhoto::factory()->create();
		$this->user = User::factory()->create();
		SizeVariant::factory()->count(7)->allSizeVariants()->create(['photo_id' => $this->photo->id]);
		$this->photo->fresh();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyAlbums();
		parent::tearDown();
	}

	public function testUrlPageLogout(): void
	{
		// In this specific case we allow to find the album but we do not display it (tested later)
		$response = $this->get(route('livewire-gallery-photo', ['albumId' => $this->album->id, 'photoId' => $this->photo->id]));
		$this->assertForbidden($response);

		$response = $this->get(route('livewire-gallery-photo', ['albumId' => '1234567890', 'photoId' => '123456']));
		$this->assertNotFound($response);
	}

	public function testPageLogout(): void
	{
		Livewire::test(Photo::class, ['albumId' => $this->album->id, 'photoId' => $this->photo->id])
			->assertForbidden();
	}

	public function testPageUserNoAccess(): void
	{
		Livewire::actingAs($this->user)->test(Photo::class, ['albumId' => $this->album->id, 'photoId' => $this->photo->id])
			->assertForbidden();
	}

	public function testPageLogin(): void
	{
		Livewire::actingAs($this->admin)->test(Photo::class, ['albumId' => SmartAlbumType::UNSORTED->value, 'photoId' => $this->photo->id])
			->assertViewIs('livewire.pages.gallery.photo')
			->assertSee($this->photo->id)
			->assertStatus(200);
	}
}
