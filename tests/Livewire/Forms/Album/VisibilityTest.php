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

use App\Livewire\Components\Forms\Album\UnlockAlbum;
use App\Livewire\Components\Forms\Album\Visibility;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class VisibilityTest extends BaseLivewireTest
{
	public function testVisibilityLoggedOut(): void
	{
		Livewire::test(Visibility::class, ['album' => $this->album1])->assertForbidden();
	}

	public function testVisibilityLoggedIn(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(Visibility::class, ['album' => $this->album1])
			->assertOk()
			->assertViewIs('livewire.forms.album.visibility')
			->toggle('is_public')
			->assertDispatched('notify', self::notifySuccess());

		$this->album1->fresh();
		$this->album1->base_class->load('access_permissions');
		$this->assertNotNull($this->album1->public_permissions());

		Livewire::actingAs($this->userMayUpload1)->test(Visibility::class, ['album' => $this->album1])
			->assertOk()
			->assertViewIs('livewire.forms.album.visibility')
			->set('grants_full_photo_access', true)
			->assertDispatched('notify', self::notifySuccess())
			->set('is_link_required', true)
			->assertDispatched('notify', self::notifySuccess())
			->set('grants_download', true)
			->assertDispatched('notify', self::notifySuccess())
			->set('is_nsfw', true)
			->assertDispatched('notify', self::notifySuccess())
			->set('is_password_required', true)
			->assertDispatched('notify', self::notifySuccess())
			->set('password', 'password')
			->assertDispatched('notify', self::notifySuccess());

		$this->album1 = $this->album1->fresh();
		$this->album1->base_class->load('access_permissions');

		Livewire::actingAs($this->userMayUpload1)->test(Visibility::class, ['album' => $this->album1])
			->assertOk()
			->assertSet('is_public', true)
			->assertSet('grants_full_photo_access', true)
			->assertSet('is_link_required', true)
			->assertSet('grants_download', true)
			->assertSet('is_nsfw', true)
			->assertSet('is_password_required', true)
			->toggle('is_public')
			->assertDispatched('notify', self::notifySuccess())
			->assertSet('grants_full_photo_access', false)
			->assertSet('is_link_required', false)
			->assertSet('grants_download', false)
			->assertSet('is_nsfw', true)
			->assertSet('is_password_required', false);
	}

	public function testUnlocking(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(Visibility::class, ['album' => $this->album1])
			->assertOk()
			->assertViewIs('livewire.forms.album.visibility')
			->toggle('is_public')
			->assertDispatched('notify', self::notifySuccess())
			->assertSet('is_public', true)
			->set('grants_full_photo_access', true)
			->assertDispatched('notify', self::notifySuccess())
			->set('is_link_required', true)
			->assertDispatched('notify', self::notifySuccess())
			->set('grants_download', true)
			->assertDispatched('notify', self::notifySuccess())
			->set('is_nsfw', true)
			->assertDispatched('notify', self::notifySuccess())
			->set('is_password_required', true)
			->assertDispatched('notify', self::notifySuccess())
			->set('password', 'password')
			->assertDispatched('notify', self::notifySuccess());

		$this->album1 = $this->album1->fresh();
		$this->album1->base_class->load('access_permissions');

		Livewire::actingAs($this->userMayUpload2)->test(UnlockAlbum::class, ['albumID' => $this->album1->id])
			->set('password', 'wrongPassword')
			->call('submit')
			->assertForbidden();

		Livewire::actingAs($this->userMayUpload2)->test(UnlockAlbum::class, ['albumID' => $this->album1->id])
			->set('password', '')
			->call('submit')
			->assertDispatched('notify');

		Livewire::actingAs($this->userMayUpload2)->test(UnlockAlbum::class, ['albumID' => $this->album1->id])
			->set('password', 'password')
			->call('submit')
			->assertOk()
			->assertRedirect(route('livewire-gallery-album', ['albumId' => $this->album1->id]));
	}

	public function testUnlockingUnlockedAlbum(): void
	{
		Livewire::actingAs($this->userMayUpload2)->test(UnlockAlbum::class, ['albumID' => $this->album1->id])
			->set('password', 'password')
			->call('submit')
			->assertForbidden();
	}
}
