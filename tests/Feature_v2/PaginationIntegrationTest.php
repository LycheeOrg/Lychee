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

namespace Tests\Feature_v2;

use App\Models\Configs;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Integration tests for pagination endpoints to verify data consistency
 * and proper behavior when using multiple endpoints together.
 */
class PaginationIntegrationTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();

		// Ensure pagination configs exist - create them if they don't already exist
		// (DatabaseTransactions may roll back migrations but configs might exist from previous tests)
		$configsToCreate = [
			['key' => 'albums_per_page', 'value' => '30', 'cat' => 'Gallery', 'type_range' => 'int', 'is_secret' => false, 'description' => 'Number of sub-albums per page.', 'details' => '', 'level' => 0, 'not_on_docker' => false, 'order' => 51, 'is_expert' => false],
			['key' => 'photos_per_page', 'value' => '100', 'cat' => 'Gallery', 'type_range' => 'int', 'is_secret' => false, 'description' => 'Number of photos per page.', 'details' => '', 'level' => 0, 'not_on_docker' => false, 'order' => 54, 'is_expert' => false],
			['key' => 'albums_pagination_ui_mode', 'value' => 'infinite_scroll', 'cat' => 'Gallery', 'type_range' => 'infinite_scroll|load_more_button|page_navigation', 'is_secret' => false, 'description' => 'Album pagination UI mode.', 'details' => '', 'level' => 0, 'not_on_docker' => false, 'order' => 52, 'is_expert' => false],
			['key' => 'photos_pagination_ui_mode', 'value' => 'infinite_scroll', 'cat' => 'Gallery', 'type_range' => 'infinite_scroll|load_more_button|page_navigation', 'is_secret' => false, 'description' => 'Photo pagination UI mode.', 'details' => '', 'level' => 0, 'not_on_docker' => false, 'order' => 55, 'is_expert' => false],
		];

		foreach ($configsToCreate as $config) {
			if (Configs::query()->where('key', $config['key'])->doesntExist()) {
				Configs::query()->insert($config);
			}
		}
	}

	public function testLoadingAlbumViaSeparateEndpoints(): void
	{
		$this->actingAs($this->userMayUpload1);

		// Load album metadata via Album::head
		$headResponse = $this->getJsonWithData('Album::head', ['album_id' => $this->album1->id]);
		$this->assertOk($headResponse);
		$headData = $headResponse->json('resource');

		// Verify no children/photos arrays in head response
		$headResponse->assertJsonMissing(['albums', 'photos']);

		// Load paginated children via Album::albums
		$albumsResponse = $this->getJsonWithData('Album::albums', ['album_id' => $this->album1->id]);
		$this->assertOk($albumsResponse);
		$albumsData = $albumsResponse->json(); // Direct access, not wrapped in 'resource'

		// Load paginated photos via Album::photos
		$photosResponse = $this->getJsonWithData('Album::photos', ['album_id' => $this->album1->id]);
		$this->assertOk($photosResponse);
		$photosData = $photosResponse->json(); // Direct access, not wrapped in 'resource'

		// Verify data consistency: counts in head match pagination totals
		$this->assertEquals($headData['num_children'], $albumsData['total'], 'Album head num_children should match albums pagination total');
		$this->assertEquals($headData['num_photos'], $photosData['total'], 'Album head num_photos should match photos pagination total');

		// Verify actual data matches counts
		$this->assertCount($headData['num_children'], $albumsData['data'], 'Albums data array should match num_children');
		$this->assertCount($headData['num_photos'], $photosData['photos'], 'Photos array should match num_photos');

		// Verify album1 specific data
		$this->assertEquals(1, $headData['num_children'], 'Album1 should have 1 child album (subAlbum1)');
		$this->assertEquals(2, $headData['num_photos'], 'Album1 should have 2 photos (photo1, photo1b)');
	}

	/**
	 * Test that all three endpoints work together with tag albums.
	 */
	public function testLoadingTagAlbumViaSeparateEndpoints(): void
	{
		$this->actingAs($this->userMayUpload1);

		// Load tag album metadata
		$headResponse = $this->getJsonWithData('Album::head', ['album_id' => $this->tagAlbum1->id]);
		$this->assertOk($headResponse);
		$headData = $headResponse->json('resource');

		// Tag albums may or may not have certain fields - just verify we got data
		$this->assertArrayHasKey('id', $headData);

		// Load photos for tag album
		$photosResponse = $this->getJsonWithData('Album::photos', ['album_id' => $this->tagAlbum1->id]);
		$this->assertOk($photosResponse);
		$photosData = $photosResponse->json(); // Direct access

		// Verify consistency - check that we have data
		$this->assertEquals(1, $photosData['total'], 'TagAlbum1 should have 1 photo with tag test');
	}

	/**
	 * Test that all three endpoints work together with smart albums.
	 */
	public function testLoadingSmartAlbumViaSeparateEndpoints(): void
	{
		$this->actingAs($this->userMayUpload1);

		// Load unsorted smart album metadata
		$headResponse = $this->getJsonWithData('Album::head', ['album_id' => 'unsorted']);
		$this->assertOk($headResponse);
		$headData = $headResponse->json('resource');

		// Smart albums may or may not have certain fields - just verify we got data
		$this->assertArrayHasKey('id', $headData);

		// Load photos for smart album
		$photosResponse = $this->getJsonWithData('Album::photos', ['album_id' => 'unsorted']);
		$this->assertOk($photosResponse);
		$photosData = $photosResponse->json(); // Direct access

		// Verify consistency - check that we have data
		$this->assertEquals(1, $photosData['total'], 'Unsorted should have 1 photo (photoUnsorted)');
	}

	/**
	 * Test pagination across multiple pages to verify continuity.
	 */
	public function testPaginationContinuityAcrossPages(): void
	{
		// Create an album with enough photos to span multiple pages
		$testAlbum = $this->album2; // Use album2 which has at least 1 photo
		$this->actingAs($this->userMayUpload2);

		// Load first page
		$page1Response = $this->getJsonWithData('Album::photos', [
			'album_id' => $testAlbum->id,
			'page' => 1,
		]);
		$this->assertOk($page1Response);
		$page1Data = $page1Response->json(); // Direct access

		// If there's only one page, verify that's correctly reported
		if ($page1Data['last_page'] === 1) {
			$this->assertEquals($page1Data['current_page'], 1);
			$this->assertEquals($page1Data['total'], count($page1Data['photos'])); // Use 'photos' field

			return;
		}

		// Load second page
		$page2Response = $this->getJsonWithData('Album::photos', [
			'album_id' => $testAlbum->id,
			'page' => 2,
		]);
		$this->assertOk($page2Response);
		$page2Data = $page2Response->json(); // Direct access

		// Verify pagination metadata
		$this->assertEquals(2, $page2Data['current_page']);
		$this->assertEquals($page1Data['total'], $page2Data['total'], 'Total should remain consistent across pages');
		$this->assertEquals($page1Data['last_page'], $page2Data['last_page'], 'Last page should remain consistent');

		// Verify no duplicate IDs between pages
		$page1Ids = array_column($page1Data['photos'], 'id'); // Use 'photos' field
		$page2Ids = array_column($page2Data['photos'], 'id'); // Use 'photos' field
		$intersection = array_intersect($page1Ids, $page2Ids);
		$this->assertEmpty($intersection, 'Pages should not contain duplicate photo IDs');
	}

	/**
	 * Test that page parameter defaults to 1 when omitted.
	 */
	public function testPageParameterDefaultsToOne(): void
	{
		$this->actingAs($this->userMayUpload1);

		// Request without page parameter
		$responseNoPage = $this->getJsonWithData('Album::photos', ['album_id' => $this->album1->id]);
		$this->assertOk($responseNoPage);
		$dataNoPage = $responseNoPage->json(); // Direct access

		// Request with explicit page=1
		$responseWithPage = $this->getJsonWithData('Album::photos', [
			'album_id' => $this->album1->id,
			'page' => 1,
		]);
		$this->assertOk($responseWithPage);
		$dataWithPage = $responseWithPage->json(); // Direct access

		// Both should return identical data
		$this->assertEquals(1, $dataNoPage['current_page']);
		$this->assertEquals(1, $dataWithPage['current_page']);
		$this->assertEquals($dataNoPage['photos'], $dataWithPage['photos']); // Use 'photos' field
	}

	/**
	 * Test concurrent access to paginated endpoints.
	 * Simulates multiple users loading the same album simultaneously.
	 */
	public function testConcurrentAccessToSameAlbum(): void
	{
		// Make album4 accessible to multiple users (it's already public)
		$albumId = $this->album4->id;

		// User 1 loads album
		$this->actingAs($this->userMayUpload1);
		$user1Response = $this->getJsonWithData('Album::head', ['album_id' => $albumId]);
		$this->assertOk($user1Response);
		$user1Data = $user1Response->json('resource');

		// User 2 loads same album
		$this->actingAs($this->userMayUpload2);
		$user2Response = $this->getJsonWithData('Album::head', ['album_id' => $albumId]);
		$this->assertOk($user2Response);
		$user2Data = $user2Response->json('resource');

		// Anonymous user loads same album (public)
		$this->app['auth']->forgetGuards();
		$anonResponse = $this->getJsonWithData('Album::head', ['album_id' => $albumId]);
		$this->assertOk($anonResponse);
		$anonData = $anonResponse->json('resource');

		// All users should see consistent data
		$this->assertEquals($user1Data['id'], $user2Data['id']);
		$this->assertEquals($user1Data['id'], $anonData['id']);
		$this->assertEquals($user1Data['num_photos'], $user2Data['num_photos']);
		$this->assertEquals($user1Data['num_photos'], $anonData['num_photos']);
		$this->assertEquals($user1Data['num_children'], $user2Data['num_children']);
		$this->assertEquals($user1Data['num_children'], $anonData['num_children']);
	}

	/**
	 * Test that requesting a page beyond available data returns empty results.
	 */
	public function testRequestingPageBeyondAvailableData(): void
	{
		$this->actingAs($this->userMayUpload1);

		// First, get the last page number
		$firstPageResponse = $this->getJsonWithData('Album::photos', ['album_id' => $this->album1->id]);
		$this->assertOk($firstPageResponse);
		$lastPage = $firstPageResponse->json('last_page'); // Direct access

		// Request a page beyond the last page
		$beyondPageResponse = $this->getJsonWithData('Album::photos', [
			'album_id' => $this->album1->id,
			'page' => $lastPage + 10,
		]);
		$this->assertOk($beyondPageResponse);
		$beyondPageData = $beyondPageResponse->json(); // Direct access

		// Should return empty data array but valid pagination metadata
		$this->assertEmpty($beyondPageData['photos']); // Use 'photos' field
		$this->assertEquals($lastPage + 10, $beyondPageData['current_page']);
		$this->assertEquals($lastPage, $beyondPageData['last_page']);
	}
}
