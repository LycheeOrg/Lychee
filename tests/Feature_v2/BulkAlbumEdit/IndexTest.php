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

use App\Models\AccessPermission;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature tests for GET /api/v2/BulkAlbumEdit.
 */
class IndexTest extends BaseApiWithDataTest
{
	// ── authentication / authorization ────────────────────────────────────────

	public function testUnauthenticatedReceives401(): void
	{
		$response = $this->getJson('BulkAlbumEdit');
		$this->assertUnauthorized($response);
	}

	public function testNonAdminReceives403(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('BulkAlbumEdit');
		$this->assertForbidden($response);
	}

	// ── basic list ────────────────────────────────────────────────────────────

	public function testAdminCanListAlbums(): void
	{
		$response = $this->actingAs($this->admin)->getJson('BulkAlbumEdit');
		$this->assertOk($response);

		$data = $response->json();
		$this->assertArrayHasKey('data', $data);
		$this->assertArrayHasKey('current_page', $data);
		$this->assertArrayHasKey('last_page', $data);
		$this->assertArrayHasKey('per_page', $data);
		$this->assertArrayHasKey('total', $data);
	}

	public function testResultsOrderedByLft(): void
	{
		$response = $this->actingAs($this->admin)->getJson('BulkAlbumEdit');
		$this->assertOk($response);

		$rows = $response->json('data');
		$lfts = array_column($rows, '_lft');
		$sorted = $lfts;
		sort($sorted);
		$this->assertSame($sorted, $lfts, 'Rows should be ordered by _lft ASC');
	}

	public function testEachRowHasExpectedFields(): void
	{
		$response = $this->actingAs($this->admin)->getJson('BulkAlbumEdit');
		$this->assertOk($response);

		$rows = $response->json('data');
		$this->assertNotEmpty($rows);

		$first = $rows[0];
		foreach (['id', 'title', 'owner_id', 'owner_name', '_lft', '_rgt', 'is_public', 'is_link_required', 'grants_full_photo_access', 'grants_download', 'grants_upload', 'created_at'] as $field) {
			$this->assertArrayHasKey($field, $first, "Missing field: {$field}");
		}
		$this->assertArrayNotHasKey('depth', $first, 'depth field must NOT be present (computed client-side)');
	}

	// ── search filter ─────────────────────────────────────────────────────────

	public function testSearchFilterReturnsMatchingAlbums(): void
	{
		// album1 title is known from BaseApiWithDataTest fixtures
		$title = $this->album1->title;
		$response = $this->actingAs($this->admin)->getJsonWithData('BulkAlbumEdit', ['search' => $title]);
		$this->assertOk($response);

		$rows = $response->json('data');
		$this->assertNotEmpty($rows);
		foreach ($rows as $row) {
			$this->assertStringContainsStringIgnoringCase($title, $row['title']);
		}
	}

	public function testSearchFilterReturnsEmptyForNonexistentTitle(): void
	{
		$response = $this->actingAs($this->admin)->getJsonWithData('BulkAlbumEdit', ['search' => 'ZZZNONEXISTENT999']);
		$this->assertOk($response);
		$this->assertSame(0, $response->json('total'));
		$this->assertSame([], $response->json('data'));
	}

	// ── visibility fields ─────────────────────────────────────────────────────

	public function testPublicAlbumShowsVisibilityTrue(): void
	{
		// Make album1 public
		$perm = AccessPermission::factory()->make([
			'base_album_id' => $this->album1->id,
			'user_id' => null,
			'user_group_id' => null,
			'is_link_required' => false,
			'grants_full_photo_access' => true,
			'grants_download' => true,
			'grants_upload' => false,
		]);
		$perm->save();

		try {
			$response = $this->actingAs($this->admin)->getJson('BulkAlbumEdit');
			$this->assertOk($response);

			$rows = $response->json('data');
			$found = null;
			foreach ($rows as $row) {
				if ($row['id'] === $this->album1->id) {
					$found = $row;
					break;
				}
			}
			$this->assertNotNull($found, 'album1 should appear in list');
			$this->assertTrue($found['is_public']);
			$this->assertTrue($found['grants_full_photo_access']);
			$this->assertTrue($found['grants_download']);
			$this->assertFalse($found['grants_upload']);
		} finally {
			AccessPermission::query()
				->where('base_album_id', $this->album1->id)
				->whereNull('user_id')
				->whereNull('user_group_id')
				->delete();
		}
	}

	public function testPrivateAlbumShowsVisibilityFalse(): void
	{
		$response = $this->actingAs($this->admin)->getJson('BulkAlbumEdit');
		$this->assertOk($response);

		$rows = $response->json('data');
		// album1 should have no public access_permissions by default in base fixtures
		$found = null;
		foreach ($rows as $row) {
			if ($row['id'] === $this->album1->id) {
				$found = $row;
				break;
			}
		}

		if ($found !== null) {
			$this->assertFalse($found['is_public']);
			$this->assertFalse($found['is_link_required']);
			$this->assertFalse($found['grants_full_photo_access']);
			$this->assertFalse($found['grants_download']);
			$this->assertFalse($found['grants_upload']);
		}
	}

	// ── pagination ────────────────────────────────────────────────────────────

	public function testPaginationMetaIsPresent(): void
	{
		$response = $this->actingAs($this->admin)->getJsonWithData('BulkAlbumEdit', ['per_page' => 25, 'page' => 1]);
		$this->assertOk($response);

		$data = $response->json();
		$this->assertSame(1, $data['current_page']);
		$this->assertGreaterThanOrEqual(1, $data['last_page']);
		$this->assertSame(25, $data['per_page']);
		$this->assertIsInt($data['total']);
	}

	public function testInvalidPerPageReturnsBadRequest(): void
	{
		$response = $this->actingAs($this->admin)->getJsonWithData('BulkAlbumEdit', ['per_page' => 7]);
		$this->assertUnprocessable($response);
	}
}
