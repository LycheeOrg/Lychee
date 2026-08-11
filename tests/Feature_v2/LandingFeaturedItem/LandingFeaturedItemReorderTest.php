<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2\LandingFeaturedItem;

use App\Models\LandingFeaturedItem;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequiresEmptyLandingFeaturedItems;

class LandingFeaturedItemReorderTest extends BaseApiWithDataTest
{
	use RequiresEmptyLandingFeaturedItems;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyLandingFeaturedItems();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyLandingFeaturedItems();
		parent::tearDown();
	}

	public function testReorderForbiddenForNonAdmin(): void
	{
		$item = LandingFeaturedItem::factory()->create();

		$response = $this->actingAs($this->userMayUpload1)->patchJson('LandingFeaturedItem/Reorder', ['ids' => [$item->id]]);
		$this->assertForbidden($response);
	}

	public function testReorderAppliesNewSortOrder(): void
	{
		$item1 = LandingFeaturedItem::factory()->create(['sort_order' => 0]);
		$item2 = LandingFeaturedItem::factory()->create(['sort_order' => 1]);

		$response = $this->actingAs($this->admin)->patchJson('LandingFeaturedItem/Reorder', [
			'ids' => [$item2->id, $item1->id],
		]);
		$this->assertOk($response);

		$this->assertSame(0, $item2->fresh()->sort_order);
		$this->assertSame(1, $item1->fresh()->sort_order);
	}

	public function testReorderRejectsPartialIdSet(): void
	{
		$item1 = LandingFeaturedItem::factory()->create(['sort_order' => 0]);
		LandingFeaturedItem::factory()->create(['sort_order' => 1]);

		$response = $this->actingAs($this->admin)->patchJson('LandingFeaturedItem/Reorder', ['ids' => [$item1->id]]);
		$this->assertUnprocessable($response);
	}
}
