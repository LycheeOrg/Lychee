<?php

declare(strict_types=1);

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Livewire\Forms\Photo;

use App\Contracts\Livewire\Params;
use App\Livewire\Components\Forms\Photo\Delete;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class DeleteTest extends BaseLivewireTest
{
	public function testDeleteLoggedOut(): void
	{
		Livewire::test(Delete::class, ['params' => [Params::ALBUM_ID => null, Params::PHOTO_ID => $this->photo1->id]])
			->assertForbidden();

		Livewire::test(Delete::class, ['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->subPhoto1->id]])
			->assertForbidden();
	}

	public function testDeleteLoggedNoRights(): void
	{
		Livewire::actingAs($this->userNoUpload)->test(Delete::class, ['params' => [Params::ALBUM_ID => null, Params::PHOTO_ID => $this->photo1->id]])
			->assertForbidden();

		Livewire::actingAs($this->userNoUpload)->test(Delete::class, ['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->subPhoto1->id]])
			->assertForbidden();

		Livewire::actingAs($this->userMayUpload2)->test(Delete::class, ['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->subPhoto1->id]])
			->assertForbidden();
	}

	public function testDeleteLoggedIn(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(Delete::class, ['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->photo1->id]])
			->assertOk()
			->assertViewIs('livewire.forms.photo.delete')
			->call('close');

		Livewire::actingAs($this->userMayUpload1)->test(Delete::class, ['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_IDS => [$this->photo1->id, $this->photo1b->id]]])
			->assertOk()
			->assertViewIs('livewire.forms.photo.delete')
			->call('submit')
			->assertRedirect(route('livewire-gallery-album', ['albumId' => $this->album1->id]));

		$this->assertCount(0, $this->album1->fresh()->load('photos')->photos);
	}
}
