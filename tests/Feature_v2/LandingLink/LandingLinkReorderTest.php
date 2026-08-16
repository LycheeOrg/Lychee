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

namespace Tests\Feature_v2\LandingLink;

use App\Models\LandingLink;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequiresEmptyLandingLinks;

class LandingLinkReorderTest extends BaseApiWithDataTest
{
	use RequiresEmptyLandingLinks;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyLandingLinks();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyLandingLinks();
		parent::tearDown();
	}

	public function testReorderForbiddenForNonAdmin(): void
	{
		$link = LandingLink::factory()->create();

		$response = $this->actingAs($this->userMayUpload1)->patchJson('LandingLink/Reorder', ['ids' => [$link->id]]);
		$this->assertForbidden($response);
	}

	public function testReorderAppliesNewSortOrder(): void
	{
		$link1 = LandingLink::factory()->create(['sort_order' => 0]);
		$link2 = LandingLink::factory()->create(['sort_order' => 1]);
		$link3 = LandingLink::factory()->create(['sort_order' => 2]);

		$response = $this->actingAs($this->admin)->patchJson('LandingLink/Reorder', [
			'ids' => [$link3->id, $link1->id, $link2->id],
		]);
		$this->assertOk($response);

		$this->assertSame(0, $link3->fresh()->sort_order);
		$this->assertSame(1, $link1->fresh()->sort_order);
		$this->assertSame(2, $link2->fresh()->sort_order);

		$response->assertJsonPath('landing_links.0.id', $link3->id);
		$response->assertJsonPath('landing_links.1.id', $link1->id);
		$response->assertJsonPath('landing_links.2.id', $link2->id);
	}

	public function testReorderRejectsPartialIdSet(): void
	{
		$link1 = LandingLink::factory()->create(['sort_order' => 0]);
		$link2 = LandingLink::factory()->create(['sort_order' => 1]);

		$response = $this->actingAs($this->admin)->patchJson('LandingLink/Reorder', ['ids' => [$link1->id]]);
		$this->assertUnprocessable($response);

		// Sort orders must be untouched: no partial application.
		$this->assertSame(0, $link1->fresh()->sort_order);
		$this->assertSame(1, $link2->fresh()->sort_order);
	}

	public function testReorderRejectsUnknownId(): void
	{
		$link1 = LandingLink::factory()->create(['sort_order' => 0]);

		$response = $this->actingAs($this->admin)->patchJson('LandingLink/Reorder', [
			'ids' => [$link1->id, 'nonexistent-id'],
		]);
		$this->assertUnprocessable($response);
	}
}
