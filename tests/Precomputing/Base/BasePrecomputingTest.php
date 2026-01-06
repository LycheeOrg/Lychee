<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Precomputing\Base;

use App\Models\User;
use Tests\Feature_v2\Base\BaseApiTest;
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
 * Base class for all precomputing tests.
 *
 * Provides access to albums, photos, and users from BaseApiWithDataTest.
 */
abstract class BasePrecomputingTest extends BaseApiTest
{
	use RequiresEmptyPurchasables;
	use RequiresEmptyOrders;
	use RequiresEmptyUsers;
	use RequiresEmptyAlbums;
	use RequiresEmptyPhotos;
	use RequiresEmptyColourPalettes;
	use RequiresEmptyLiveMetrics;
	use RequiresEmptyWebAuthnCredentials;
	use RequiresEmptyGroups;
	use RequiresEmptyTags;
	use RequiresEmptyRenamerRules;

	public User $admin;

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
		// This admin user is super important, As without it we cannot compute max access right.
		$this->admin = User::factory()->may_administrate()->create();
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
