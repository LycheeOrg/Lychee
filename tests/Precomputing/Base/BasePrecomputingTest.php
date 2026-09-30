<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Precomputing\Base;

use App\Models\User;
use Illuminate\Support\Facades\DB;
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

	/**
	 * Ranks 1–3 of the album's max-privilege cover: its precomputed
	 * `album_user_thumbs` row keyed on the owner (Feature 076, ADR-076-01).
	 *
	 * @return array{0:string|null,1:string|null,2:string|null}
	 */
	protected function maxCovers(string|\App\Models\Album $album): array
	{
		$album_id = is_string($album) ? $album : $album->id;
		$owner_id = DB::table('base_albums')->where('id', '=', $album_id)->value('owner_id');

		return $this->coverTriple(DB::table('album_user_thumbs')->where('user_id', '=', $owner_id), $album_id);
	}

	/**
	 * Ranks 1–3 of the album's least-privilege cover: its precomputed row
	 * not keyed on the owner (`NULL` or the single shared user).
	 *
	 * @return array{0:string|null,1:string|null,2:string|null}
	 */
	protected function leastCovers(string|\App\Models\Album $album): array
	{
		$album_id = is_string($album) ? $album : $album->id;
		$owner_id = DB::table('base_albums')->where('id', '=', $album_id)->value('owner_id');

		return $this->coverTriple(DB::table('album_user_thumbs')->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', '<>', $owner_id)), $album_id);
	}

	/**
	 * @return array{0:string|null,1:string|null,2:string|null}
	 */
	private function coverTriple(\Illuminate\Database\Query\Builder $query, string $album_id): array
	{
		$row = $query->where('album_id', '=', $album_id)->where('is_precomputed', '=', true)->first();

		return [$row?->photo_id, $row?->photo_id_2, $row?->photo_id_3];
	}
}
