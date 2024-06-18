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
use App\Enum\SmartAlbumType;
use App\Livewire\Components\Forms\Photo\Move;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class MoveTest extends BaseLivewireTest
{
	public function testMoveLoggedOut(): void
	{
		Livewire::test(Move::class, ['params' => [Params::ALBUM_ID => null, Params::PHOTO_ID => $this->photo1->id]])
			->assertForbidden();

		Livewire::test(Move::class, ['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->subPhoto1->id]])
			->assertForbidden();
	}

	public function testMoveLoggedNoRights(): void
	{
		Livewire::actingAs($this->userNoUpload)->test(Move::class, ['params' => [Params::ALBUM_ID => null, Params::PHOTO_ID => $this->photo1->id]])
			->assertForbidden();

		Livewire::actingAs($this->userNoUpload)->test(Move::class, ['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->subPhoto1->id]])
			->assertForbidden();

		Livewire::actingAs($this->userMayUpload2)->test(Move::class, ['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->subPhoto1->id]])
			->assertForbidden();
	}

	public function testMoveLoggedIn(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(Move::class, ['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->subPhoto1->id]])
			->assertOk()
			->assertViewIs('livewire.forms.photo.move')
			->call('close');

		Livewire::actingAs($this->userMayUpload1)->test(Move::class, ['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_IDS => [$this->photo1->id, $this->photo1b->id]]])
			->assertOk()
			->assertViewIs('livewire.forms.photo.move')
			->call('setAlbum', $this->album2->id, $this->album2->title)
			->assertRedirect(route('livewire-gallery-album', ['albumId' => $this->album1->id]));

		$this->assertCount(0, $this->album1->fresh()->load('photos')->photos);
		$this->assertCount(3, $this->album2->fresh()->load('photos')->photos); // 2 added + 1 old

		Livewire::actingAs($this->userMayUpload1)->test(Move::class, ['params' => [Params::ALBUM_ID => null, Params::PHOTO_ID => $this->photoUnsorted->id]])
			->assertOk()
			->assertViewIs('livewire.forms.photo.move')
			->call('setAlbum', $this->album2->id, $this->album2->title)
			->assertRedirect(route('livewire-gallery-album', ['albumId' => SmartAlbumType::UNSORTED->value]));

		$this->assertCount(4, $this->album2->fresh()->load('photos')->photos); // 1 added + 3 old (from above)
	}
}
