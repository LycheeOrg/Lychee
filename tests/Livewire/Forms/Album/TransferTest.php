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

use App\Enum\Livewire\NotificationType;
use App\Livewire\Components\Forms\Album\Transfer;
use App\Livewire\Components\Pages\Gallery\Album;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class TransferTest extends BaseLivewireTest
{
	public function testTransferLoggedOut(): void
	{
		Livewire::test(Transfer::class, ['album' => $this->album1])
			->assertForbidden();
	}

	public function testTransferLoggedNoRights(): void
	{
		Livewire::actingAs($this->userNoUpload)->test(Transfer::class, ['album' => $this->album1])
			->assertForbidden();

		Livewire::actingAs($this->userMayUpload2)->test(Transfer::class, ['album' => $this->album1])
			->assertForbidden();
	}

	public function testTransferLoggedIn(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(Transfer::class, ['album' => $this->album1])
			->assertOk()
			->assertViewIs('livewire.forms.album.transfer')
			->set('username', $this->userMayUpload2->username)
			->assertOk()
			->call('transfer')
			->assertRedirect(route('livewire-gallery'));

		// user 2 has access
		Livewire::actingAs($this->userMayUpload2)->test(Album::class, ['albumId' => $this->album1->id])
			->assertViewIs('livewire.pages.gallery.album')
			->assertSee($this->album1->id)
			->assertOk()
			->assertSet('flags.is_accessible', true);

		// User 1 no longer have access.
		Livewire::actingAs($this->userMayUpload1)->test(Album::class, ['albumId' => $this->album1->id])
			->assertRedirect(route('livewire-gallery'));
	}

	public function testTransferAsAdmin(): void
	{
		Livewire::actingAs($this->admin)->test(Transfer::class, ['album' => $this->album1])
			->assertOk()
			->assertViewIs('livewire.forms.album.transfer')
			->set('username', $this->userMayUpload2->username)
			->assertOk()
			->call('transfer')
			->assertDispatched('notify', ['msg' => 'Transfer successful!', 'type' => NotificationType::SUCCESS->value]);
	}
}
