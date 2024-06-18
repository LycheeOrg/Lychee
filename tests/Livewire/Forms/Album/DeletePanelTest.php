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

use App\Livewire\Components\Forms\Album\DeletePanel;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class DeletePanelTest extends BaseLivewireTest
{
	public function testDeletePanelLoggedOut(): void
	{
		Livewire::test(DeletePanel::class, ['album' => $this->album1])
			->assertForbidden();
	}

	public function testDeletePanelLoggedNoRights(): void
	{
		Livewire::actingAs($this->userNoUpload)->test(DeletePanel::class, ['album' => $this->album1])
			->assertForbidden();

		Livewire::actingAs($this->userMayUpload2)->test(DeletePanel::class, ['album' => $this->album1])
			->assertForbidden();
	}

	public function testDeletePanelLoggedIn(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(DeletePanel::class, ['album' => $this->subAlbum1])
			->assertOk()
			->assertViewIs('livewire.forms.album.delete-panel')
			->call('delete')
			->assertRedirect(route('livewire-gallery-album', ['albumId' => $this->album1->id]));

		$this->assertCount(0, $this->album1->fresh()->load('children')->children);

		Livewire::actingAs($this->userMayUpload1)->test(DeletePanel::class, ['album' => $this->album1])
			->assertOk()
			->assertViewIs('livewire.forms.album.delete-panel')
			->call('delete')
			->assertRedirect(route('livewire-gallery'));
	}
}
