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

namespace Tests\Livewire\Base;

use App\Enum\Livewire\NotificationType;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;
use Tests\Feature\Traits\RequiresEmptyAlbums;
use Tests\Feature\Traits\RequiresEmptyPhotos;
use Tests\Feature\Traits\RequiresEmptyUsers;

abstract class BaseLivewireTest extends AbstractTestCase
{
	use RequiresEmptyUsers;
	use RequiresEmptyAlbums;
	use RequiresEmptyPhotos;
	use DatabaseTransactions;

	protected User $admin;

	protected Album $album1;
	protected Album $album2;
	protected Album $subAlbum1;
	protected Album $subAlbum2;
	protected Photo $photo1;
	protected Photo $photo1b;
	protected Photo $photo2;
	protected Photo $subPhoto1;
	protected Photo $subPhoto2;
	protected Photo $photoUnsorted;

	protected User $userLocked;
	protected User $userMayUpload1;
	protected User $userMayUpload2;
	protected User $userNoUpload;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyUsers();
		$this->setUpRequiresEmptyAlbums();
		$this->setUpRequiresEmptyPhotos();

		$this->admin = User::factory()->may_administrate()->create();
		$this->userMayUpload1 = User::factory()->may_upload()->create();
		$this->userMayUpload2 = User::factory()->may_upload()->create();
		$this->userNoUpload = User::factory()->create();
		$this->userLocked = User::factory()->locked()->create();

		$this->album1 = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$this->photo1 = Photo::factory()->owned_by($this->userMayUpload1)->with_GPS_coordinates()->in($this->album1)->create();
		$this->photo1b = Photo::factory()->owned_by($this->userMayUpload1)->with_subGPS_coordinates()->in($this->album1)->create();

		$this->subAlbum1 = Album::factory()->children_of($this->album1)->owned_by($this->userMayUpload1)->create();
		$this->subPhoto1 = Photo::factory()->owned_by($this->userMayUpload1)->with_GPS_coordinates()->in($this->subAlbum1)->create();

		$this->album2 = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$this->photo2 = Photo::factory()->owned_by($this->userMayUpload1)->with_GPS_coordinates()->in($this->album2)->create();

		$this->subAlbum2 = Album::factory()->children_of($this->album2)->owned_by($this->userMayUpload1)->create();
		$this->subPhoto2 = Photo::factory()->owned_by($this->userMayUpload1)->with_GPS_coordinates()->in($this->subAlbum2)->create();

		$this->photoUnsorted = Photo::factory()->owned_by($this->userMayUpload1)->with_GPS_coordinates()->create();

		$this->withoutVite();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyPhotos();
		$this->tearDownRequiresEmptyAlbums();
		$this->tearDownRequiresEmptyUsers();

		parent::tearDown();
	}

	final public static function notifySuccess(): array
	{
		return ['msg' => __('lychee.CHANGE_SUCCESS'), 'type' => NotificationType::SUCCESS->value];
	}
}