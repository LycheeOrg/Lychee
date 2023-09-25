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
use App\Livewire\Components\Forms\Album\ShareWith;
use App\Livewire\Components\Forms\Album\ShareWithLine;
use App\Livewire\Components\Pages\Sharing;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\Traits\RequiresEmptyAlbums;
use Tests\Feature\Traits\RequiresEmptyPhotos;
use Tests\Feature\Traits\RequiresEmptyUsers;
use Tests\Livewire\Base\BaseLivewireTest;
use Tests\Livewire\Traits\CreateAlbum;
use Tests\Livewire\Traits\CreateTree;

class ShareWithTest extends BaseLivewireTest
{
	use RequiresEmptyAlbums;
	use RequiresEmptyUsers;
	use RequiresEmptyPhotos;
	use CreateAlbum;
	use CreateTree;

	private Album $album;
	private Album $subAlbum;
	private Photo $photo;
	private Photo $subPhoto;
	private User $user;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyUsers();
		$this->setUpRequiresEmptyAlbums();
		$this->setUpRequiresEmptyPhotos();

		$this->album = $this->createAlbum();
		$this->subAlbum = $this->createAlbum();
		$this->user = User::factory()->create(['username' => 'user', 'may_upload' => true]);

		$this->photo = Photo::factory()->create(['latitude' => '51.81738000', 'longitude' => '5.86694306', 'altitude' => '83.1000']);
		SizeVariant::factory()->count(7)->allSizeVariants()->create(['photo_id' => $this->photo->id]);
		$this->photo->fresh();

		$this->subPhoto = Photo::factory()->create(['latitude' => '51.81738000', 'longitude' => '5.86694306', 'altitude' => '83.1000']);
		SizeVariant::factory()->count(7)->allSizeVariants()->create(['photo_id' => $this->subPhoto->id]);
		$this->subPhoto->fresh();

		$this->createTree();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyPhotos();
		$this->tearDownRequiresEmptyAlbums();
		$this->tearDownRequiresEmptyUsers();
		parent::tearDown();
	}

	public function testShareWithLoggedOut(): void
	{
		Livewire::test(ShareWith::class, ['album' => $this->album])->assertForbidden();
	}

	public function testShareWithLoggedIn(): void
	{
		Livewire::actingAs($this->admin)->test(ShareWith::class, ['album' => $this->album])
			->assertOk()
			->assertViewIs('livewire.forms.album.share-with')
			->set('search', 'a')
			->assertOk()
			->set('search', 'u')
			->call('select', $this->user->id, $this->user->username)
			->assertSet('userID', $this->user->id)
			->assertSet('username', $this->user->username)
			->call('clearUsername')
			->assertSet('userID', null)
			->assertSet('username', null)
			->call('select', $this->user->id, $this->user->username)
			->set('grants_full_photo_access', true)
			->set('grants_download', true)
			->set('grants_upload', true)
			->set('grants_edit', true)
			->set('grants_delete', true)
			->call('add')
			->assertOk();

		$this->album->fresh();
		$this->album->base_class->load('access_permissions');

		// we do have one permission
		$this->assertEquals(1, $this->album->access_permissions->count());
		/** @var AccessPermission $perm */
		$perm = $this->album->access_permissions->first();

		$this->assertEquals($this->user->id, $perm->user_id);
		$this->assertTrue($perm->grants_full_photo_access);
		$this->assertTrue($perm->grants_download);
		$this->assertTrue($perm->grants_upload);
		$this->assertTrue($perm->grants_edit);
		$this->assertTrue($perm->grants_delete);
		$this->assertNotNull($perm->album);

		Livewire::actingAs($this->user)->test(ShareWithLine::class, ['perm' => $perm])
			->assertForbidden();

		Livewire::actingAs($this->admin)->test(ShareWithLine::class, ['perm' => $perm])
			->assertOk()
			->set('grants_full_photo_access', false)
			->set('grants_download', false)
			->set('grants_upload', false)
			->set('grants_edit', false)
			->set('grants_delete', false)
			->assertDispatched('notify', ['msg' => __('lychee.CHANGE_SUCCESS'), 'type' => NotificationType::SUCCESS->value])
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

		Livewire::actingAs($this->user)->test(Sharing::class)
			->assertOk()
			->call('delete', $perm->id)
			->assertForbidden();

		Livewire::actingAs($this->admin)->test(ShareWith::class, ['album' => $this->album])
			->assertOk()
			->call('delete', $perm->id)
			->assertOk();
	}
}
