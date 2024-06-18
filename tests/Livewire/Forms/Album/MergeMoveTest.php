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

namespace Tests\Livewire\Forms\Album;

use App\Contracts\Livewire\Params;
use App\Livewire\Components\Forms\Album\Merge;
use App\Livewire\Components\Forms\Album\Move;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class MergeMoveTest extends BaseLivewireTest
{
	public function testMergeLoggedOut(): void
	{
		Livewire::test(Merge::class, ['params' => [Params::PARENT_ID => null, Params::ALBUM_ID => $this->album1->id]])
			->assertForbidden();

		Livewire::test(Merge::class, ['params' => [Params::PARENT_ID => $this->album1->id, Params::ALBUM_ID => $this->subAlbum1->id]])
			->assertForbidden();

		Livewire::test(Merge::class, ['params' => [Params::PARENT_ID => null, Params::ALBUM_IDS => [$this->album1->id, $this->subAlbum1->id]]])
			->assertForbidden();
	}

	public function testMoveLoggedOut(): void
	{
		Livewire::test(Move::class, ['params' => [Params::PARENT_ID => null, Params::ALBUM_ID => $this->album1->id]])
			->assertForbidden();

		Livewire::test(Move::class, ['params' => [Params::PARENT_ID => $this->album1->id, Params::ALBUM_ID => $this->subAlbum1->id]])
			->assertForbidden();
	}

	public function testMergeLoggedNoRights(): void
	{
		Livewire::actingAs($this->userNoUpload)->test(Merge::class, ['params' => [Params::PARENT_ID => null, Params::ALBUM_ID => $this->album1->id]])
			->assertForbidden();

		Livewire::actingAs($this->userNoUpload)->test(Merge::class, ['params' => [Params::PARENT_ID => $this->album1->id, Params::ALBUM_ID => $this->subAlbum1->id]])
			->assertForbidden();

		Livewire::actingAs($this->userMayUpload2)->test(Merge::class, ['params' => [Params::PARENT_ID => $this->album1->id, Params::ALBUM_ID => $this->subAlbum1->id]])
			->assertForbidden();
	}

	public function testMoveLoggedNoRights(): void
	{
		Livewire::actingAs($this->userNoUpload)->test(Move::class, ['params' => [Params::PARENT_ID => null, Params::ALBUM_ID => $this->album1->id]])
			->assertForbidden();

		Livewire::actingAs($this->userNoUpload)->test(Move::class, ['params' => [Params::PARENT_ID => $this->album1->id, Params::ALBUM_ID => $this->subAlbum1->id]])
			->assertForbidden();

		Livewire::actingAs($this->userMayUpload2)->test(Move::class, ['params' => [Params::PARENT_ID => $this->album1->id, Params::ALBUM_ID => $this->subAlbum1->id]])
			->assertForbidden();
	}

	public function testMergeLoggedIn(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(Merge::class, ['params' => [Params::PARENT_ID => $this->album1->id, Params::ALBUM_ID => $this->subAlbum1->id]])
			->assertOk()
			->assertViewIs('livewire.forms.album.merge')
			->call('close');

		Livewire::actingAs($this->userMayUpload1)->test(Merge::class, ['params' => [Params::PARENT_ID => $this->album1->id, Params::ALBUM_ID => $this->subAlbum1->id]])
			->assertOk()
			->assertViewIs('livewire.forms.album.merge')
			->call('setAlbum', $this->album1->id, $this->album1->title)
			->assertSet('albumID', $this->album1->id)
			->assertSet('title', $this->album1->title)
			->assertOk()
			->call('submit')
			->assertRedirect(route('livewire-gallery-album', ['albumId' => $this->album1->id]));

		$this->assertCount(0, $this->album1->fresh()->load('children')->children);

		Livewire::actingAs($this->userMayUpload1)->test(Merge::class, ['params' => [Params::PARENT_ID => null, Params::ALBUM_ID => $this->album1->id]])
			->assertOk()
			->assertViewIs('livewire.forms.album.merge')
			->call('setAlbum', $this->album2->id, $this->album2->title)
			->assertSet('albumID', $this->album2->id)
			->assertSet('title', $this->album2->title)
			->assertOk()
			->call('submit')
			->assertRedirect(route('livewire-gallery'));

		$this->assertCount(1, $this->album2->fresh()->load('children')->children);
	}

	public function testMoveLoggedIn(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(Move::class, ['params' => [Params::PARENT_ID => $this->album1->id, Params::ALBUM_ID => $this->subAlbum1->id]])
			->assertOk()
			->assertViewIs('livewire.forms.album.move')
			->call('close');

		Livewire::actingAs($this->userMayUpload1)->test(Move::class, ['params' => [Params::PARENT_ID => $this->album1->id, Params::ALBUM_ID => $this->subAlbum1->id]])
			->assertOk()
			->assertViewIs('livewire.forms.album.move')
			->call('setAlbum', $this->album2->id, $this->album2->title)
			->assertSet('albumID', $this->album2->id)
			->assertSet('title', $this->album2->title)
			->assertOk()
			->call('submit')
			->assertRedirect(route('livewire-gallery-album', ['albumId' => $this->album1->id]));

		$this->assertCount(0, $this->album1->fresh()->load('children')->children);
		$this->assertCount(2, $this->album2->fresh()->load('children')->children);

		Livewire::actingAs($this->userMayUpload1)->test(Move::class, ['params' => [Params::PARENT_ID => null, Params::ALBUM_ID => $this->album1->id]])
			->assertOk()
			->assertViewIs('livewire.forms.album.move')
			->call('setAlbum', $this->album2->id, $this->album2->title)
			->assertSet('albumID', $this->album2->id)
			->assertSet('title', $this->album2->title)
			->assertOk()
			->call('submit')
			->assertRedirect(route('livewire-gallery'));

		$this->assertCount(3, $this->album2->fresh()->load('children')->children);
	}
}
