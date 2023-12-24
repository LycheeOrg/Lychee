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

namespace Tests\Livewire\Forms\Photo;

use App\Contracts\Livewire\Params;
use App\Livewire\Components\Forms\Photo\Download;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class DownloadTest extends BaseLivewireTest
{
	public function testDeleteLoggedOut(): void
	{
		Livewire::test(Download::class, ['params' => [Params::ALBUM_ID => null, Params::PHOTO_ID => $this->photo1->id]])
			->assertForbidden();

		Livewire::test(Download::class, ['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_IDS => [$this->subPhoto1->id]]])
			->assertForbidden();
	}

	public function testDeleteLoggedNoRights(): void
	{
		Livewire::actingAs($this->userNoUpload)->test(Download::class, ['params' => [Params::ALBUM_ID => null, Params::PHOTO_ID => $this->photo1->id]])
			->assertForbidden();

		Livewire::actingAs($this->userNoUpload)->test(Download::class, ['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->subPhoto1->id]])
			->assertForbidden();

		Livewire::actingAs($this->userMayUpload2)->test(Download::class, ['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->subPhoto1->id]])
			->assertForbidden();
	}

	public function testDeleteLoggedIn(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(Download::class, ['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->photo1->id]])
			->assertOk()
			->assertViewIs('livewire.forms.photo.download')
			->call('close');

		Livewire::actingAs($this->userMayUpload1)->test(Download::class, ['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_IDS => [$this->photo1->id, $this->photo1b->id]]])
			->assertRedirect();
	}
}
