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
use App\Livewire\Components\Forms\Album\Visibility;
use App\Models\Album;
use Livewire\Livewire;
use Tests\Feature\Traits\RequiresEmptyAlbums;
use Tests\Livewire\Base\BaseLivewireTest;
use Tests\Livewire\Traits\CreateAlbum;

class VisibilityTest extends BaseLivewireTest
{
	use RequiresEmptyAlbums;
	use CreateAlbum;

	private Album $album;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyAlbums();
		$this->album = $this->createAlbum();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyAlbums();
		parent::tearDown();
	}

	public function testVisibilityLoggedOut(): void
	{
		Livewire::test(Visibility::class, ['album' => $this->album])->assertForbidden();
	}

	public function testVisibilityLoggedIn(): void
	{
		Livewire::actingAs($this->admin)->test(Visibility::class, ['album' => $this->album])
			->assertOk()
			->assertViewIs('livewire.forms.album.visibility')
			->toggle('is_public')
			->assertDispatched('notify', ['msg' => __('lychee.CHANGE_SUCCESS'), 'type' => NotificationType::SUCCESS->value]);

		$this->album->fresh();
		$this->album->base_class->load('access_permissions');
		$this->assertNotNull($this->album->public_permissions());

		Livewire::actingAs($this->admin)->test(Visibility::class, ['album' => $this->album])
			->assertOk()
			->assertViewIs('livewire.forms.album.visibility')
			->set('grants_full_photo_access', true)
			->assertDispatched('notify', ['msg' => __('lychee.CHANGE_SUCCESS'), 'type' => NotificationType::SUCCESS->value])
			->set('is_link_required', true)
			->assertDispatched('notify', ['msg' => __('lychee.CHANGE_SUCCESS'), 'type' => NotificationType::SUCCESS->value])
			->set('grants_download', true)
			->assertDispatched('notify', ['msg' => __('lychee.CHANGE_SUCCESS'), 'type' => NotificationType::SUCCESS->value])
			->set('is_nsfw', true)
			->assertDispatched('notify', ['msg' => __('lychee.CHANGE_SUCCESS'), 'type' => NotificationType::SUCCESS->value])
			->set('is_password_required', true)
			->assertDispatched('notify', ['msg' => __('lychee.CHANGE_SUCCESS'), 'type' => NotificationType::SUCCESS->value])
			->set('password', 'password')
			->assertDispatched('notify', ['msg' => __('lychee.CHANGE_SUCCESS'), 'type' => NotificationType::SUCCESS->value]);

		$this->album = $this->album->fresh();
		$this->album->base_class->load('access_permissions');

		Livewire::actingAs($this->admin)->test(Visibility::class, ['album' => $this->album])
			->assertOk()
			->assertSet('is_public', true)
			->assertSet('grants_full_photo_access', true)
			->assertSet('is_link_required', true)
			->assertSet('grants_download', true)
			->assertSet('is_nsfw', true)
			->assertSet('is_password_required', true)
			->toggle('is_public')
			->assertDispatched('notify', ['msg' => __('lychee.CHANGE_SUCCESS'), 'type' => NotificationType::SUCCESS->value])
			->assertSet('grants_full_photo_access', false)
			->assertSet('is_link_required', false)
			->assertSet('grants_download', false)
			->assertSet('is_nsfw', true)
			->assertSet('is_password_required', false);
	}
}
