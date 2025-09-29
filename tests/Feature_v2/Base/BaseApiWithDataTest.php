<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

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

use App\Enum\UserGroupRole;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Palette;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\TagAlbum;
use App\Models\User;
use App\Models\UserGroup;
use Tests\Traits\InteractWithSmartAlbums;
use Tests\Traits\RequiresEmptyAlbums;
use Tests\Traits\RequiresEmptyColourPalettes;
use Tests\Traits\RequiresEmptyGroups;
use Tests\Traits\RequiresEmptyLiveMetrics;
use Tests\Traits\RequiresEmptyOrders;
use Tests\Traits\RequiresEmptyPhotos;
use Tests\Traits\RequiresEmptyPurchasables;
use Tests\Traits\RequiresEmptyRenamerRules;
use Tests\Traits\RequiresEmptyTags;
use Tests\Traits\RequiresEmptyUsers;
use Tests\Traits\RequiresEmptyWebAuthnCredentials;

/**
 * BaseApiWithDataTest serves as a foundational testing class for Lychee's API features.
 *
 * Goal: This class provides a comprehensive test environment with pre-configured data
 * structures to simulate various user scenarios and permissions within the Lychee photo
 * management system.
 *
 * Architecture:
 * - Extends BaseApiTest for core API testing functionality
 * - Incorporates multiple traits for test environment setup and teardown
 * - Creates a complete hierarchical data structure with users, albums, photos, permissions, and groups
 *
 * Data Structure Graph:
 * - 👥 Users:
 *   ├─ 👑 admin (god allmighty)
 *   │  └─ 📁 album5 (root)
 *   │
 *   ├─ 👤 userMayUpload1
 *   │  ├─ 📁 album1 (root)
 *   │  │  ├─ 🔑 perm1 (👤 userMayUpload2)
 *   │  │  ├─ 🔑 perm11 (👥 group1 → album1)
 *   │  │  ├─ 🖼️ photo1 (🏷️ `test`, 🎨 palette)
 *   │  │  ├─ 🖼️ photo1b
 *   │  │  └─ 📁 subAlbum1
 *   │  │     └─ 🖼️ subPhoto1
 *   │  ├─ 🏷️ tagAlbum1 (linked to `test`)
 *   │  └─ 🖼️ photoUnsorted
 *   │
 *   ├─ 👤 userMayUpload2
 *   │  └─ 📁 album2 (root)
 *   │     ├─ 🖼️ photo2
 *   │     └─ 📁 subAlbum2
 *   │        └─ 🖼️ subPhoto2
 *   │
 *   ├─ 👤 userNoUpload
 *   │  └─ 📁 album3 (root)
 *   │     └─ 🖼️ photo3
 *   │
 *   └─ 👤 userLocked
 *      └─ 📁 album4 (root)
 *         ├─ 🔑 perm4 (🌐 public visibility)
 *         ├─ 🖼️ photo4
 *         └─ 📁 subAlbum4 (🌐 public visibility)
 *            ├─ 🔑 perm44 (🌐 public visibility)
 *            └─ 🖼️ subPhoto4
 * - 👥 Groups:
 *   ├─ 👥 group1 (contains 👤 userWithGroup1, 👤 userWithGroupAdmin )
 *   └─ 👥 group2
 */
abstract class BaseApiWithDataTest extends BaseApiTest
{
	use RequiresEmptyPurchasables;
	use RequiresEmptyOrders;
	use RequiresEmptyUsers;
	use RequiresEmptyAlbums;
	use RequiresEmptyPhotos;
	use RequiresEmptyColourPalettes;
	use RequiresEmptyLiveMetrics;
	use RequiresEmptyWebAuthnCredentials;
	use InteractWithSmartAlbums;
	use RequiresEmptyGroups;
	use RequiresEmptyTags;
	use RequiresEmptyRenamerRules;

	protected User $admin;
	protected User $userMayUpload1;
	protected User $userMayUpload2;
	protected User $userNoUpload;
	protected User $userLocked;

	// album 1 belongs to userMayUpload1
	protected Album $album1;
	protected Album $subAlbum1;
	protected TagAlbum $tagAlbum1;
	protected Photo $photo1;
	protected Photo $photo1b;
	protected Photo $subPhoto1;
	protected Photo $photoUnsorted;

	protected Palette $palette1;

	// album 2 belongs to userMayUpload2
	protected Album $album2;
	protected Album $subAlbum2;
	protected Photo $photo2;
	protected Photo $subPhoto2;

	// album 3 belongs to userNoUpload
	protected Album $album3;
	protected Photo $photo3;

	// Tags
	protected Tag $tag_test;

	// album 4 belongs to userLocked
	// album 4 is visible without being logged in
	protected Album $album4;
	protected Album $subAlbum4;
	protected Photo $photo4;
	protected Photo $subPhoto4;

	// album 5 belongs to admin and is empty
	protected Album $album5;

	protected AccessPermission $perm1;
	protected AccessPermission $perm4;
	protected AccessPermission $perm44;
	protected AccessPermission $perm11;

	protected UserGroup $group1;
	protected UserGroup $group2;
	protected User $userWithGroupAdmin;
	protected User $userWithGroup1;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyUsers();
		$this->setUpRequiresEmptyAlbums();
		$this->setUpRequiresEmptyPhotos();
		$this->setUpRequiresEmptyColourPalettes();
		$this->setUpRequiresEmptyLiveMetrics();
		$this->setUpRequiresEmptyGroups();
		$this->setUpRequiresEmptyTags();
		$this->setUpRequiresEmptyRenamerRules();
		$this->setUpRequiresEmptyOrders();
		$this->setUpRequiresEmptyPurchasables();

		$this->admin = User::factory()->may_administrate()->create();
		$this->userMayUpload1 = User::factory()->may_upload()->create();
		$this->userMayUpload2 = User::factory()->may_upload()->create();
		$this->userNoUpload = User::factory()->create();
		$this->userLocked = User::factory()->locked()->create();

		$this->group1 = UserGroup::factory()->create();
		$this->group2 = UserGroup::factory()->create();
		$this->userWithGroup1 = User::factory()->with_group($this->group1)->create();
		$this->userWithGroupAdmin = User::factory()->with_group($this->group1, UserGroupRole::ADMIN)->create();

		$this->tag_test = Tag::factory()->with_name('test')->create();

		$this->album1 = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$this->photo1 = Photo::factory()->owned_by($this->userMayUpload1)->with_GPS_coordinates()->with_tags([$this->tag_test])->with_palette()->in($this->album1)->create();
		$this->palette1 = $this->photo1->palette;
		$this->photo1b = Photo::factory()->owned_by($this->userMayUpload1)->with_subGPS_coordinates()->in($this->album1)->create();
		$this->subAlbum1 = Album::factory()->children_of($this->album1)->owned_by($this->userMayUpload1)->create();
		$this->subPhoto1 = Photo::factory()->owned_by($this->userMayUpload1)->with_GPS_coordinates()->in($this->subAlbum1)->create();
		$this->tagAlbum1 = TagAlbum::factory()->owned_by($this->userMayUpload1)->of_tags([$this->tag_test])->create();

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

		$this->perm4 = AccessPermission::factory()->public()->visible()->for_album($this->album4)->create();
		$this->perm44 = AccessPermission::factory()->public()->visible()->for_album($this->subAlbum4)->create();

		$this->perm1 = AccessPermission::factory()
			->for_user($this->userMayUpload2)
			->for_album($this->album1)
			->visible()
			->grants_edit()
			->grants_delete()
			->grants_upload()
			->grants_download()
			->grants_full_photo()
			->create();

		$this->perm11 = AccessPermission::factory()
			->for_user_group($this->group1)
			->for_album($this->album1)
			->visible()
			->grants_edit()
			->grants_delete()
			->grants_upload()
			->grants_download()
			->grants_full_photo()
			->create();

		$this->album5 = Album::factory()->as_root()->owned_by($this->admin)->create();

		Configs::set('owner_id', $this->admin->id);

		$this->withoutVite();
		$this->clearCachedSmartAlbums();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyOrders();
		$this->tearDownRequiresEmptyPurchasables();
		$this->tearDownRequiresEmptyRenamerRules();
		$this->tearDownRequiresEmptyTags();
		$this->tearDownRequiresEmptyLiveMetrics();
		$this->tearDownRequiresEmptyColourPalettes();
		$this->tearDownRequiresEmptyPhotos();
		$this->tearDownRequiresEmptyAlbums();
		$this->tearDownRequiresEmptyUsers();
		$this->tearDownRequiresEmptyGroups();

		parent::tearDown();
	}
}
