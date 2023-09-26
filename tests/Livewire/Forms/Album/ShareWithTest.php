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

use App\Livewire\Components\Forms\Album\ShareWith;
use App\Livewire\Components\Forms\Album\ShareWithLine;
use App\Livewire\Components\Pages\Sharing;
use App\Models\AccessPermission;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class ShareWithTest extends BaseLivewireTest
{
	public function testShareWithLoggedOut(): void
	{
		Livewire::test(ShareWith::class, ['album' => $this->album1])->assertForbidden();
	}

	public function testShareWithLoggedIn(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(ShareWith::class, ['album' => $this->album1])
			->assertOk()
			->assertViewIs('livewire.forms.album.share-with')
			->set('search', 'a')
			->assertOk()
			->set('search', 'u')
			->call('select', $this->userMayUpload2->id, $this->userMayUpload2->username)
			->assertSet('userID', $this->userMayUpload2->id)
			->assertSet('username', $this->userMayUpload2->username)
			->call('clearUsername')
			->assertSet('userID', null)
			->assertSet('username', null)
			->call('select', $this->userMayUpload2->id, $this->userMayUpload2->username)
			->set('grants_full_photo_access', true)
			->set('grants_download', true)
			->set('grants_upload', true)
			->set('grants_edit', true)
			->set('grants_delete', true)
			->call('add')
			->assertOk();

		$this->album1->fresh();
		$this->album1->base_class->load('access_permissions');

		// we do have one permission
		$this->assertEquals(1, $this->album1->access_permissions->count());
		/** @var AccessPermission $perm */
		$perm = $this->album1->access_permissions->first();

		$this->assertEquals($this->userMayUpload2->id, $perm->user_id);
		$this->assertTrue($perm->grants_full_photo_access);
		$this->assertTrue($perm->grants_download);
		$this->assertTrue($perm->grants_upload);
		$this->assertTrue($perm->grants_edit);
		$this->assertTrue($perm->grants_delete);
		$this->assertNotNull($perm->album);

		Livewire::actingAs($this->userMayUpload2)->test(ShareWithLine::class, ['perm' => $perm])
			->assertForbidden();

		Livewire::actingAs($this->userMayUpload1)->test(ShareWithLine::class, ['perm' => $perm])
			->assertOk()
			->set('grants_full_photo_access', false)
			->set('grants_download', false)
			->set('grants_upload', false)
			->set('grants_edit', false)
			->set('grants_delete', false)
			->assertDispatched('notify', self::notifySuccess())
			->assertSet('grants_full_photo_access', false)
			->assertSet('grants_download', false)
			->assertSet('grants_upload', false)
			->assertSet('grants_edit', false)
			->assertSet('grants_delete', false);

		$perm = $perm->fresh();
		$this->assertFalse($perm->grants_full_photo_access);
		$this->assertFalse($perm->grants_download);
		$this->assertFalse($perm->grants_upload);
		$this->assertFalse($perm->grants_edit);
		$this->assertFalse($perm->grants_delete);

		Livewire::actingAs($this->userNoUpload)->test(Sharing::class)
			->assertForbidden();

		Livewire::actingAs($this->userMayUpload2)->test(Sharing::class)
			->assertOk()
			->call('delete', $perm->id)
			->assertForbidden();

		Livewire::actingAs($this->userMayUpload1)->test(ShareWith::class, ['album' => $this->album1])
			->assertOk()
			->call('delete', $perm->id)
			->assertOk();
	}
}
