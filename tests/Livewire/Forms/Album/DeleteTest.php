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

namespace Tests\Livewire\Forms\Album;

use App\Contracts\Livewire\Params;
use App\Livewire\Components\Forms\Album\Delete;
use Livewire\Livewire;
use function PHPUnit\Framework\assertCount;
use Tests\Livewire\Base\BaseLivewireTest;

class DeleteTest extends BaseLivewireTest
{
	public function testDeleteLoggedOut(): void
	{
		Livewire::test(Delete::class, ['params' => [Params::PARENT_ID => null, Params::ALBUM_ID => $this->album1->id]])
			->assertForbidden();

		Livewire::test(Delete::class, ['params' => [Params::PARENT_ID => $this->album1->id, Params::ALBUM_ID => $this->subAlbum1->id]])
			->assertForbidden();
	}

	public function testDeleteLoggedNoRights(): void
	{
		Livewire::actingAs($this->userNoUpload)->test(Delete::class, ['params' => [Params::PARENT_ID => null, Params::ALBUM_ID => $this->album1->id]])
			->assertForbidden();

		Livewire::actingAs($this->userNoUpload)->test(Delete::class, ['params' => [Params::PARENT_ID => $this->album1->id, Params::ALBUM_ID => $this->subAlbum1->id]])
			->assertForbidden();

		Livewire::actingAs($this->userMayUpload2)->test(Delete::class, ['params' => [Params::PARENT_ID => $this->album1->id, Params::ALBUM_ID => $this->subAlbum1->id]])
			->assertForbidden();
	}

	public function testDeleteLoggedIn(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(Delete::class, ['params' => [Params::PARENT_ID => $this->album1->id, Params::ALBUM_ID => $this->subAlbum1->id]])
			->assertOk()
			->assertViewIs('livewire.forms.album.delete')
			->call('close');

		Livewire::actingAs($this->userMayUpload1)->test(Delete::class, ['params' => [Params::PARENT_ID => $this->album1->id, Params::ALBUM_ID => $this->subAlbum1->id]])
			->assertOk()
			->assertViewIs('livewire.forms.album.delete')
			->call('delete')
			->assertRedirect(route('livewire-gallery-album', ['albumId' => $this->album1->id]));

		assertCount(0, $this->album1->fresh()->load('children')->children);
	}
}
