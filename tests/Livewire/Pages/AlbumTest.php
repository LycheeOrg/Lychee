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

use App\Livewire\Components\Pages\Gallery\Album;
use App\Models\Album as ModelsAlbum;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\Traits\RequiresEmptyAlbums;
use Tests\Feature\Traits\RequiresEmptyUsers;
use Tests\Livewire\Base\BaseLivewireTest;
use Tests\Livewire\Traits\CreateAlbum;

class AlbumTest extends BaseLivewireTest
{
	use RequiresEmptyAlbums;
	use RequiresEmptyUsers;
	use CreateAlbum;

	private ModelsAlbum $album;
	private User $user;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyAlbums();

		$this->album = $this->createAlbum();
		$this->user = User::factory()->create();
	}

	public function tearDown(): void
	{
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
			->assertStatus(200)
			->assertSet('flags.is_accessible', false)
			->dispatch('reloadPage');
	}

	public function testPageUserNoAccess(): void
	{
		Livewire::actingAs($this->user)->test(Album::class, ['albumId' => $this->album->id])
			->assertRedirect(route('livewire-gallery'));
	}

	public function testPageLogin(): void
	{
		Livewire::actingAs($this->admin)->test(Album::class, ['albumId' => $this->album->id])
			->assertViewIs('livewire.pages.gallery.album')
			->assertSee($this->album->id)
			->assertStatus(200)
			->assertSet('flags.is_accessible', true)
			->call('silentUpdate')
			->assertStatus(200);
	}
}
