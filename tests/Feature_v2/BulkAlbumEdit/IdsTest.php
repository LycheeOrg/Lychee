<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2\BulkAlbumEdit;

use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature tests for GET /api/v2/BulkAlbumEdit::ids.
 */
class IdsTest extends BaseApiWithDataTest
{
	// ── authentication / authorization ────────────────────────────────────────

	public function testUnauthenticatedReceives401(): void
	{
		$response = $this->getJson('BulkAlbumEdit::ids');
		$this->assertUnauthorized($response);
	}

	public function testNonAdminReceives403(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('BulkAlbumEdit::ids');
		$this->assertForbidden($response);
	}

	// ── basic list ────────────────────────────────────────────────────────────

	public function testAdminCanListIds(): void
	{
		$response = $this->actingAs($this->admin)->getJson('BulkAlbumEdit::ids');
		$this->assertOk($response);

		$data = $response->json();
		$this->assertArrayHasKey('ids', $data);
		$this->assertArrayHasKey('capped', $data);
		$this->assertIsArray($data['ids']);
		$this->assertIsBool($data['capped']);
	}

	public function testIdsNotCappedWhenFewAlbums(): void
	{
		$response = $this->actingAs($this->admin)->getJson('BulkAlbumEdit::ids');
		$this->assertOk($response);

		$this->assertFalse($response->json('capped'));
	}

	public function testIdsContainKnownAlbums(): void
	{
		$response = $this->actingAs($this->admin)->getJson('BulkAlbumEdit::ids');
		$this->assertOk($response);

		$ids = $response->json('ids');
		$this->assertContains($this->album1->id, $ids);
	}

	// ── search filter ─────────────────────────────────────────────────────────

	public function testSearchFilterAppliedToIds(): void
	{
		$title = $this->album1->title;
		$response = $this->actingAs($this->admin)->getJsonWithData('BulkAlbumEdit::ids', ['search' => $title]);
		$this->assertOk($response);

		$ids = $response->json('ids');
		$this->assertContains($this->album1->id, $ids);
	}

	public function testSearchFilterReturnsEmptyForNonexistent(): void
	{
		$response = $this->actingAs($this->admin)->getJsonWithData('BulkAlbumEdit::ids', ['search' => 'ZZZNON999']);
		$this->assertOk($response);

		$this->assertSame([], $response->json('ids'));
		$this->assertFalse($response->json('capped'));
	}
}
