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

use App\Constants\AccessPermissionConstants as APC;
use App\Contracts\Livewire\Params;
use App\Livewire\Components\Forms\Album\Create;
use App\Livewire\Components\Forms\Album\ShareWith;
use App\Livewire\Components\Pages\Gallery\Album as GalleryAlbum;
use App\Models\AccessPermission;
use App\Models\Album;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class CreateTest extends BaseLivewireTest
{
	public function testCreateLoggedOut(): void
	{
		Livewire::test(Create::class, ['params' => [Params::PARENT_ID => null]])
			->assertForbidden();

		Livewire::test(Create::class, ['params' => [Params::PARENT_ID => $this->album1->id]])
			->assertForbidden();
	}

	public function testCreateLoggedNoRights(): void
	{
		Livewire::actingAs($this->userNoUpload)->test(Create::class, ['params' => [Params::PARENT_ID => null]])
			->assertForbidden();

		Livewire::actingAs($this->userNoUpload)->test(Create::class, ['params' => [Params::PARENT_ID => $this->album1->id]])
			->assertForbidden();

		Livewire::actingAs($this->userMayUpload2)->test(Create::class, ['params' => [Params::PARENT_ID => $this->album1->id]])
			->assertForbidden();
	}

	public function testCreateLoggedIn(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(Create::class, ['params' => [Params::PARENT_ID => $this->album1->id]])
			->assertOk()
			->assertViewIs('livewire.forms.add.create')
			->call('close')
			->assertDispatched('closeModal');

		Livewire::actingAs($this->userMayUpload1)->test(Create::class, ['params' => [Params::PARENT_ID => $this->album1->id]])
			->assertOk()
			->assertViewIs('livewire.forms.add.create')
			->set('title', fake()->country() . ' ' . fake()->year())
			->call('submit')
			->assertRedirect();

		$this->assertCount(2, $this->album1->fresh()->load('children')->children);
	}

	public function testCreateViaAccessRights(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->admin)->create();

		Livewire::actingAs($this->admin)->test(ShareWith::class, ['album' => $album])
			->assertOk()
			->set('userID', $this->userMayUpload1->id)
			->set('grants_full_photo_access', true)
			->set('grants_download', true)
			->set('grants_upload', true)
			->set('grants_edit', true)
			->set('grants_delete', true)
			->call('add')
			->assertOk();

		$num = AccessPermission::query()
			->where(APC::BASE_ALBUM_ID, '=', $album->id)
			->where(APC::USER_ID, '=', $this->userMayUpload1->id)
			->count();

		// we do have one permission
		$this->assertEquals(1, $num);

		Livewire::actingAs($this->userMayUpload1)->test(GalleryAlbum::class, ['albumId' => $album->id])
			->assertOk();

		$title = fake()->country() . ' ' . fake()->year();
		Livewire::actingAs($this->userMayUpload1)->test(Create::class, ['params' => [Params::PARENT_ID => $album->id]])
			->assertOk()
			->assertViewIs('livewire.forms.add.create')
			->set('title', $title)
			->call('submit')
			->assertRedirect();

		$subAlbum = Album::query()
			->select(['albums.*'])
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->where('base_albums.title', '=', $title)->first();

		Livewire::actingAs($this->userMayUpload1)->test(GalleryAlbum::class, ['albumId' => $subAlbum->id])
			->assertOk();

		Livewire::actingAs($this->userMayUpload1)->test(GalleryAlbum::class, ['albumId' => $album->id])
			->assertOk()
			->assertSee($title);
	}
}
