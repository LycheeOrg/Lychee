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
use App\Models\Album as ModelsAlbum;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\Traits\RequiresEmptyAlbums;
use Tests\Feature\Traits\RequiresEmptyPhotos;
use Tests\Feature\Traits\RequiresEmptyUsers;
use Tests\Livewire\Base\BaseLivewireTest;
use Tests\Livewire\Traits\CreateAlbum;

class AlbumTest extends BaseLivewireTest
{
	use RequiresEmptyAlbums;
	use RequiresEmptyUsers;
	use RequiresEmptyPhotos;
	use CreateAlbum;

	private ModelsAlbum $album;
	private ModelsAlbum $subAlbum;
	private Photo $photo;
	private Photo $subPhoto;
	private User $user;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyAlbums();
		$this->setUpRequiresEmptyPhotos();

		$this->album = $this->createAlbum();
		$this->subAlbum = $this->createAlbum();
		$this->user = User::factory()->create();

		$this->photo = Photo::factory()->create(['latitude' => '51.81738000', 'longitude' => '5.86694306', 'altitude' => '83.1000']);
		SizeVariant::factory()->count(7)->allSizeVariants()->create(['photo_id' => $this->photo->id]);
		$this->photo->fresh();

		$this->subPhoto = Photo::factory()->create(['latitude' => '51.81738000', 'longitude' => '5.86694306', 'altitude' => '83.1000']);
		SizeVariant::factory()->count(7)->allSizeVariants()->create(['photo_id' => $this->subPhoto->id]);
		$this->subPhoto->fresh();
	}

	private function buildTree(): void
	{
		$this->photo->album_id = $this->album->id;
		$this->photo->save();
		$this->photo = $this->photo->fresh();

		$this->subPhoto->album_id = $this->subAlbum->id;
		$this->subPhoto->save();
		$this->subPhoto = $this->subPhoto->fresh();

		$this->album->appendNode($this->subAlbum);
		$this->album->save();
		$this->album->fixOwnershipOfChildren();
		$this->album = $this->album->fresh();
		$this->album->load('children', 'photos');
		$this->subAlbum->load('children', 'photos');
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyPhotos();
		$this->tearDownRequiresEmptyAlbums();
		parent::tearDown();
	}

	public function testUrlPageLogout(): void
	{
		// In this specific case we allow to find the album but we do not display it (tested later)
		$response = $this->get(route('livewire-gallery-album', ['albumId' => $this->album->id]));
		$this->assertOk($response);

		$response = $this->get(route('livewire-gallery-album', ['albumId' => '1234567890']));
		$this->assertNotFound($response);
	}

	public function testPageLogout(): void
	{
		Livewire::test(Album::class, ['albumId' => $this->album->id])
			->assertViewIs('livewire.pages.gallery.album')
			->assertSee($this->album->id)
			->assertOk()
			->assertSet('flags.is_accessible', false)
			->dispatch('reloadPage');
	}

	public function testPageUserNoAccess(): void
	{
		Livewire::actingAs($this->user)->test(Album::class, ['albumId' => $this->album->id])
			->assertRedirect(route('livewire-gallery'));
	}

	public function testPageLoginAndBack(): void
	{
		Livewire::actingAs($this->admin)->test(Album::class, ['albumId' => $this->album->id])
			->assertViewIs('livewire.pages.gallery.album')
			->assertSee($this->album->id)
			->assertOk()
			->assertSet('flags.is_accessible', true)
			->call('silentUpdate')
			->assertOk()
			->call('back')
			->assertRedirect(Albums::class);
	}

	public function testPageLoginAndBackFromSubAlbum(): void
	{
		$this->buildTree();

		Livewire::actingAs($this->admin)->test(Album::class, ['albumId' => $this->subAlbum->id])
			->assertViewIs('livewire.pages.gallery.album')
			->assertSee($this->subAlbum->id)
			->assertOk()
			->assertSet('flags.is_accessible', true)
			->call('silentUpdate')
			->assertOk()
			->call('back')
			->assertRedirect(Album::class, ['albumId' => $this->album->id]);
	}

	public function testMenus(): void
	{
		$this->buildTree();

		Livewire::actingAs($this->admin)->test(Album::class, ['albumId' => $this->album->id])
			->assertViewIs('livewire.pages.gallery.album')
			->assertSee($this->album->id)
			->assertSee($this->subAlbum->id)
			->assertSee($this->photo->id)
			->assertSee($this->subPhoto->size_variants->getThumb()->url)
			->assertOk()
			->call('loadAlbum', 1900)
			->assertOk()
			->call('openContextMenu')
			->assertOk()
			->call('openAlbumDropdown', 0, 0, $this->subAlbum->id)
			->assertOk()
			->call('openAlbumsDropdown', 0, 0, [$this->subAlbum->id])
			->assertOk()
			->call('openPhotoDropdown', 0, 0, $this->photo->id)
			->assertOk()
			->call('openPhotosDropdown', 0, 0, [$this->photo->id])
			->assertOk()
			->call('setStar', [$this->photo->id])
			->assertOk()
			->call('unsetStar', [$this->photo->id])
			->assertOk()
			->call('setCover', $this->photo->id)
			->assertDispatched('notify')
			->assertOk();
	}

	public function testActions(): void
	{
		$this->buildTree();

		Livewire::actingAs($this->admin)->test(Album::class, ['albumId' => $this->album->id])
			->assertViewIs('livewire.pages.gallery.album')
			->assertSee($this->album->id)
			->assertSee($this->subAlbum->id)
			->assertSee($this->photo->id)
			->assertSee($this->subPhoto->size_variants->getThumb()->url)
			->assertOk()
			->call('loadAlbum', 1900)
			->assertOk()
			->call('setStar', [$this->photo->id])
			->assertOk()
			->call('unsetStar', [$this->photo->id])
			->assertOk()
			->call('setCover', $this->photo->id)
			->assertDispatched('notify')
			->assertOk();
	}

	public function testActionsUnsorted(): void
	{
		Configs::set('layout', AlbumLayoutType::SQUARE);

		Livewire::actingAs($this->admin)->test(Album::class, ['albumId' => SmartAlbumType::UNSORTED->value])
			->assertViewIs('livewire.pages.gallery.album')
			->assertSee($this->photo->id)
			->assertOk()
			->call('loadAlbum', 1900)
			->assertOk()
			->call('setStar', [$this->photo->id])
			->assertOk()
			->call('unsetStar', [$this->photo->id])
			->assertOk()
			->call('setCover', $this->photo->id)
			->assertNotDispatched('notify')
			->assertOk();

		Configs::set('layout', AlbumLayoutType::JUSTIFIED);
	}
}
