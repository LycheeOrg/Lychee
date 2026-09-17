<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
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

namespace Tests\Feature_v3\Flow;

use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Support\Carbon;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers `GET /Flow` (Feature 068, FR-068-01).
 *
 * Reuses the shared fixture graph from `BaseApiWithDataTest` (album1, owned
 * by userMayUpload1, 2 direct photos; album4, owned by userLocked, public,
 * 1 direct photo) — same fixture `Tests\Feature_v2\Flow\FlowTest` uses for
 * the equivalent v2 scenarios, so results are directly comparable.
 */
class FlowV3Test extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		config(['features.struct-of-array' => true]);
	}

	public function testFlagOffReturns403(): void
	{
		config(['features.struct-of-array' => false]);

		// Authenticated, so a false authorize() maps to 403 (not 401, which
		// this app reserves for an unauthenticated caller — see
		// testGuestWithoutFlowPublicReturns401 below), isolating the flag
		// gate itself from flow_enabled/flow_public's own auth gating.
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Flow');
		$this->assertForbidden($response);
	}

	public function testGuestWithoutFlowPublicReturns401(): void
	{
		$response = $this->getJsonV3('Flow');
		$this->assertUnauthorized($response);
	}

	public function testAnonymousRootMatchesV2Scope(): void
	{
		Configs::set('flow_public', true);

		$response = $this->getJsonV3('Flow');
		$this->assertOk($response);
		$json = $response->json();

		self::assertSame([$this->album4->id], $json['ids']);
		self::assertArrayNotHasKey('photos', $json);

		Configs::set('flow_public', false);
	}

	public function testUserRootMatchesV2Scope(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Flow');
		$this->assertOk($response);
		$json = $response->json();

		self::assertEqualsCanonicalizing([$this->album1->id, $this->album4->id], $json['ids']);
		self::assertArrayNotHasKey('photos', $json);

		// Every album-indexed field must stay index-aligned with `ids`.
		self::assertCount(2, $json['titles']);
		self::assertCount(2, $json['num_photos']);
	}

	public function testFieldsForOneAlbum(): void
	{
		Configs::set('flow_public', true);

		$response = $this->getJsonV3('Flow');
		$this->assertOk($response);
		$json = $response->json();

		$index = array_search($this->album4->id, $json['ids'], true);
		self::assertNotFalse($index);
		self::assertSame($this->album4->title, $json['titles'][$index]);
		self::assertSame(1, $json['num_photos'][$index]);
		// album4 has one direct child, subAlbum4.
		self::assertSame(1, $json['num_children'][$index]);
		// Guest: no owner name.
		self::assertNull($json['owner_names'][$index]);

		Configs::set('flow_public', false);
	}

	public function testAuthenticatedUserSeesOwnerName(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Flow');
		$this->assertOk($response);
		$json = $response->json();

		$index = array_search($this->album1->id, $json['ids'], true);
		self::assertNotFalse($index);
		self::assertNotNull($json['owner_names'][$index]);
	}

	// ── Strategy toggle preserves data (Feature 068, FR-068-18, S-068-14) ──

	public function testTogglingStrategyPreservesPublishedAtAndReordersCorrectly(): void
	{
		Configs::set('flow_public', true);
		Configs::set('flow_strategy', 'opt-in');

		$older = Carbon::parse('2020-01-01T00:00:00+00:00');
		$newer = Carbon::parse('2026-01-01T00:00:00+00:00');
		$this->album4->published_at = $older;
		$this->album4->save();

		// A second public album with photos, published later.
		$album_new = Album::factory()->as_root()->owned_by($this->userLocked)->create();
		Photo::factory()->owned_by($this->userLocked)->in($album_new)->create();
		AccessPermission::factory()->public()->visible()->for_album($album_new)->create();
		$album_new->published_at = $newer;
		$album_new->save();

		$response = $this->getJsonV3('Flow');
		$this->assertOk($response);
		// OPT_IN orders by published_at desc: newer first.
		self::assertSame([$album_new->id, $this->album4->id], $response->json('ids'));

		// Switch to auto: published_at must survive untouched, ordering
		// switches to created_at (Flow::do()'s AUTO branch ignores published_at
		// entirely - it is not cleared, just not read).
		Configs::set('flow_strategy', 'auto');
		$this->getJsonV3('Flow')->assertOk();

		self::assertTrue($older->eq($this->album4->fresh()->published_at));
		self::assertTrue($newer->eq($album_new->fresh()->published_at));

		// Switch back to opt-in: same published_at values, same ordering.
		Configs::set('flow_strategy', 'opt-in');
		$response = $this->getJsonV3('Flow');
		$this->assertOk($response);
		self::assertSame([$album_new->id, $this->album4->id], $response->json('ids'));

		Configs::set('flow_public', false);
		Configs::set('flow_strategy', 'auto');
	}
}
