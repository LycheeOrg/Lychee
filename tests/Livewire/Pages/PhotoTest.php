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

use App\Contracts\Livewire\Params;
use App\Livewire\Components\Modules\Photo\Properties;
use App\Livewire\Components\Pages\Gallery\Photo;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class PhotoTest extends BaseLivewireTest
{
	public function testUrlPageLogout(): void
	{
		// In this specific case we allow to find the album but we do not display it (tested later)
		$response = $this->get(route('livewire-gallery-photo', ['albumId' => $this->album1->id, 'photoId' => $this->photo1->id]));
		$this->assertForbidden($response);

		$response = $this->get(route('livewire-gallery-photo', ['albumId' => '1234567890', 'photoId' => '123456']));
		$this->assertNotFound($response);
	}

	public function testPageLogout(): void
	{
		Livewire::test(Photo::class, ['albumId' => $this->album1->id, 'photoId' => $this->photo1->id])
			->assertForbidden();
	}

	public function testPageUserNoAccess(): void
	{
		Livewire::actingAs($this->userMayUpload2)->test(Photo::class,
			['albumId' => $this->album1->id, 'photoId' => $this->photo1->id])
			->assertForbidden();
	}

	public function testPageLogin(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(Photo::class,
			['albumId' => $this->album1->id, 'photoId' => $this->photo1->id])
			->assertViewIs('livewire.pages.gallery.photo')
			->assertSee($this->photo1->id)
			->assertOk()
			->dispatch('reloadPage')
			->assertOk()
			->call('set_star')
			->assertSet('photo.is_starred', true)
			->call('set_star')
			->assertSet('photo.is_starred', false)
			->call('move')
			->assertDispatched('openModal', 'forms.photo.move', '', [Params::PHOTO_IDS => [$this->photo1->id], Params::ALBUM_ID => $this->album1->id])
			->call('delete')
			->assertDispatched('openModal', 'forms.photo.delete', '', [Params::PHOTO_IDS => [$this->photo1->id], Params::ALBUM_ID => $this->album1->id])
			->call('back')
			->assertRedirect(route('livewire-gallery-album', ['albumId' => $this->album1->id]));
	}

	public function testPropertiesLogout(): void
	{
		Livewire::test(Properties::class, ['photo' => $this->photo1])
			->assertForbidden();

		Livewire::actingAs($this->userMayUpload2)->test(Properties::class, ['photo' => $this->photo1])
			->assertForbidden();
	}

	public function testProperties(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(Properties::class, ['photo' => $this->photo1])
			->assertOk()
			->call('submit')
			->assertDispatched('notify', self::notifySuccess());
	}
}
