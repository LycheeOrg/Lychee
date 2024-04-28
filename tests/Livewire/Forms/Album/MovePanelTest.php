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

use App\Livewire\Components\Forms\Album\MovePanel;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class MovePanelTest extends BaseLivewireTest
{
	public function testMoveLoggedOut(): void
	{
		Livewire::test(MovePanel::class, ['album' => $this->subAlbum1])
			->assertForbidden();

		Livewire::test(MovePanel::class, ['album' => $this->subAlbum1])
			->assertForbidden();
	}

	public function testMoveLoggedNoRights(): void
	{
		Livewire::actingAs($this->userNoUpload)->test(MovePanel::class, ['album' => $this->subAlbum1])
			->assertForbidden();

		Livewire::actingAs($this->userMayUpload2)->test(MovePanel::class, ['album' => $this->subAlbum1])
			->assertForbidden();
	}

	public function testMoveLoggedIn(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(MovePanel::class, ['album' => $this->subAlbum1])
			->assertOk()
			->assertViewIs('livewire.forms.album.move-panel')
			->call('setAlbum', $this->album2->id, $this->album2->title)
			->assertSet('albumID', $this->album2->id)
			->assertSet('title', $this->album2->title)
			->assertOk()
			->call('move')
			->assertRedirect(route('livewire-gallery-album', ['albumId' => $this->album1->id]));

		$this->assertCount(0, $this->album1->fresh()->load('children')->children);
		$this->assertCount(2, $this->album2->fresh()->load('children')->children);

		Livewire::actingAs($this->userMayUpload1)->test(MovePanel::class, ['album' => $this->album1])
			->assertOk()
			->assertViewIs('livewire.forms.album.move-panel')
			->call('setAlbum', $this->album2->id, $this->album2->title)
			->assertSet('albumID', $this->album2->id)
			->assertSet('title', $this->album2->title)
			->assertOk()
			->call('move')
			->assertRedirect(route('livewire-gallery'));

		$this->assertCount(3, $this->album2->fresh()->load('children')->children);
	}
}
