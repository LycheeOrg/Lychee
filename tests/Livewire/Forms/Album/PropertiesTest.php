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
use App\Livewire\Components\Forms\Album\Properties;
use App\Models\Album;
use App\Models\Photo;
use App\Models\SizeVariant;
use Livewire\Livewire;
use Tests\Feature\Traits\RequiresEmptyAlbums;
use Tests\Feature\Traits\RequiresEmptyPhotos;
use Tests\Feature\Traits\RequiresEmptyUsers;
use Tests\Livewire\Base\BaseLivewireTest;
use Tests\Livewire\Traits\CreateAlbum;
use Tests\Livewire\Traits\CreateTree;

class PropertiesTest extends BaseLivewireTest
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

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyAlbums();
		$this->setUpRequiresEmptyPhotos();

		$this->album = $this->createAlbum();
		$this->subAlbum = $this->createAlbum();

		$this->photo = Photo::factory()->create(['latitude' => '51.81738000', 'longitude' => '5.86694306', 'altitude' => '83.1000']);
		SizeVariant::factory()->count(7)->allSizeVariants()->create(['photo_id' => $this->photo->id]);
		$this->photo->fresh();

		$this->subPhoto = Photo::factory()->create(['latitude' => '51.81738000', 'longitude' => '5.86694306', 'altitude' => '83.1000']);
		SizeVariant::factory()->count(7)->allSizeVariants()->create(['photo_id' => $this->subPhoto->id]);
		$this->subPhoto->fresh();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyPhotos();
		$this->tearDownRequiresEmptyAlbums();
		parent::tearDown();
	}

	public function testPropertiesLoggedOut(): void
	{
		Livewire::test(Properties::class, ['album' => $this->album])->assertForbidden();
	}

	public function testPropertiesLoggedIn(): void
	{
		Livewire::actingAs($this->admin)->test(Properties::class, ['album' => $this->album])
			->assertOk()
			->assertViewIs('livewire.forms.album.properties')
			->call('submit')
			->assertOk()
			->assertDispatched('notify', ['msg' => __('lychee.CHANGE_SUCCESS'), 'type' => NotificationType::SUCCESS->value]);
	}
}
