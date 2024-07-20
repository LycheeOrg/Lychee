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

namespace Tests\Feature_v2\Base;

use App\Enum\Livewire\NotificationType;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;
use Tests\Traits\InteractWithSmartAlbums;
use Tests\Traits\RequiresEmptyAlbums;
use Tests\Traits\RequiresEmptyPhotos;
use Tests\Traits\RequiresEmptyUsers;

abstract class BaseV2Test extends AbstractTestCase
{
	use RequiresEmptyUsers;
	use RequiresEmptyAlbums;
	use RequiresEmptyPhotos;
	use DatabaseTransactions;
	use InteractWithSmartAlbums;

	protected User $admin;
	protected User $userMayUpload1;
	protected User $userMayUpload2;
	protected User $userNoUpload;
	protected User $userLocked;

	// album 1 belongs to userMayUpload1
	protected Album $album1;
	protected Album $subAlbum1;
	protected Photo $photo1;
	protected Photo $photo1b;
	protected Photo $subPhoto1;
	protected Photo $photoUnsorted;

	// album 2 belongs to userMayUpload2
	protected Album $album2;
	protected Album $subAlbum2;
	protected Photo $photo2;
	protected Photo $subPhoto2;

	// album 3 belongs to userNoUpload
	protected Album $album3;
	protected Photo $photo3;

	// album 4 belongs to userLocked
	// album 4 is visible without being logged in
	protected Album $album4;
	protected Album $subAlbum4;
	protected Photo $photo4;
	protected Photo $subPhoto4;

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

		$this->album2 = Album::factory()->as_root()->owned_by($this->userMayUpload2)->create();
		$this->photo2 = Photo::factory()->owned_by($this->userMayUpload2)->with_GPS_coordinates()->in($this->album2)->create();

		$this->subAlbum2 = Album::factory()->children_of($this->album2)->owned_by($this->userMayUpload2)->create();
		$this->subPhoto2 = Photo::factory()->owned_by($this->userMayUpload2)->with_GPS_coordinates()->in($this->subAlbum2)->create();

		$this->photoUnsorted = Photo::factory()->owned_by($this->userMayUpload1)->with_GPS_coordinates()->create();

		$this->album3 = Album::factory()->as_root()->owned_by($this->userNoUpload)->create();
		$this->photo3 = Photo::factory()->owned_by($this->userNoUpload)->with_GPS_coordinates()->in($this->album3)->create();

		$this->album4 = Album::factory()->as_root()->owned_by($this->userLocked)->create();
		$this->photo4 = Photo::factory()->owned_by($this->userLocked)->with_GPS_coordinates()->in($this->album4)->create();
		$this->subAlbum4 = Album::factory()->children_of($this->album4)->owned_by($this->userLocked)->create();
		$this->subPhoto4 = Photo::factory()->owned_by($this->userLocked)->with_GPS_coordinates()->in($this->subAlbum4)->create();

		AccessPermission::factory()->public()->visible()->for_album($this->album4)->create();
		AccessPermission::factory()->public()->visible()->for_album($this->subAlbum4)->create();

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