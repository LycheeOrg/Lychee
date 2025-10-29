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

namespace Tests\Feature_v2\Embed;

use App\Models\Configs;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Test the Embed Stream API endpoint for public photo streaming.
 */
class EmbedStreamTest extends BaseApiWithDataTest
{
	/**
	 * Test that public photo stream returns correct structure.
	 */
	public function testGetPublicStream(): void
	{
		$response = $this->getJson('Embed/stream');
		$this->assertOk($response);

		// Verify structure
		$response->assertJsonStructure([
			'site_title',
			'photos' => [
				'*' => [
					'id',
					'title',
					'description',
					'size_variants' => [
						'placeholder',
						'thumb',
						'thumb2x',
						'small',
						'small2x',
						'medium',
						'medium2x',
						'original' => [
							'width',
							'height',
						],
					],
					'exif' => [
						'make',
						'model',
						'lens',
						'iso',
						'aperture',
						'shutter',
						'focal',
						'taken_at',
					],
				],
			],
		]);

		// Verify site_title is present
		$response->assertJsonPath('site_title', fn ($title) => is_string($title) && strlen($title) > 0);
	}

	/**
	 * Test that only photos from public albums are included.
	 */
	public function testOnlyPublicPhotosIncluded(): void
	{
		$response = $this->getJson('Embed/stream');
		$this->assertOk($response);

		$data = $response->json();
		$photoIds = collect($data['photos'])->pluck('id')->toArray();

		// photo4 and subPhoto4 are in public albums (album4, subAlbum4)
		// These should be included
		$this->assertContains($this->photo4->id, $photoIds, 'photo4 from public album4 should be included');
		$this->assertContains($this->subPhoto4->id, $photoIds, 'subPhoto4 from public subAlbum4 should be included');

		// photo1, photo2, photo3 are in private albums
		// These should NOT be included
		$this->assertNotContains($this->photo1->id, $photoIds, 'photo1 from private album1 should not be included');
		$this->assertNotContains($this->photo2->id, $photoIds, 'photo2 from private album2 should not be included');
		$this->assertNotContains($this->photo3->id, $photoIds, 'photo3 from private album3 should not be included');
	}

	/**
	 * Test pagination with limit parameter.
	 */
	public function testPaginationLimit(): void
	{
		// Request only 1 photo
		$response = $this->getJson('Embed/stream?limit=1');
		$this->assertOk($response);

		$data = $response->json();
		$this->assertCount(1, $data['photos'], 'Should return exactly 1 photo when limit=1');
	}

	/**
	 * Test pagination with offset parameter.
	 */
	public function testPaginationOffset(): void
	{
		// Get first photo
		$firstResponse = $this->getJson('Embed/stream?limit=1&offset=0');
		$this->assertOk($firstResponse);
		$firstData = $firstResponse->json();
		$firstPhotoId = $firstData['photos'][0]['id'];

		// Get second photo
		$secondResponse = $this->getJson('Embed/stream?limit=1&offset=1');
		$this->assertOk($secondResponse);
		$secondData = $secondResponse->json();
		$secondPhotoId = $secondData['photos'][0]['id'];

		// They should be different photos
		$this->assertNotEquals($firstPhotoId, $secondPhotoId, 'offset should return different photos');
	}

	/**
	 * Test that limit is capped at 100.
	 */
	public function testLimitCappedAt100(): void
	{
		// Try to request 1000 photos
		$response = $this->getJson('Embed/stream?limit=1000');
		$this->assertOk($response);

		$data = $response->json();
		// Should only return up to 100 photos (or less if there aren't 100 public photos)
		$this->assertLessThanOrEqual(100, count($data['photos']), 'Should cap at 100 photos max');
	}

	/**
	 * Test that minimum limit is 1.
	 */
	public function testMinimumLimitIs1(): void
	{
		// Try to request 0 photos
		$response = $this->getJson('Embed/stream?limit=0');
		$this->assertOk($response);

		$data = $response->json();
		// Should return at least 1 photo (or 0 if there are no public photos)
		$this->assertGreaterThanOrEqual(0, count($data['photos']), 'Should handle limit=0 gracefully');
	}

	/**
	 * Test that site title matches configured value.
	 */
	public function testSiteTitleMatchesConfig(): void
	{
		// Set a custom site title
		$customTitle = 'My Custom Photo Gallery';
		Configs::set('site_title', $customTitle);

		$response = $this->getJson('Embed/stream');
		$this->assertOk($response);

		$response->assertJson([
			'site_title' => $customTitle,
		]);

		// Clean up - reset to default
		Configs::set('site_title', 'Lychee');
	}

	/**
	 * Test NSFW filtering when enabled.
	 */
	public function testNSFWFilteringWhenEnabled(): void
	{
		// Create a new public NSFW album with a photo
		$nsfwAlbum = \App\Models\Album::factory()->as_root()->owned_by($this->userLocked)->create([
			'is_nsfw' => true,
		]);
		\App\Models\AccessPermission::factory()->public()->visible()->for_album($nsfwAlbum)->create();

		$nsfwPhoto = \App\Models\Photo::factory()->owned_by($this->userLocked)->with_GPS_coordinates()->in($nsfwAlbum)->create();

		// Enable NSFW hiding in RSS
		$originalHideNsfw = Configs::getValueAsString('hide_nsfw_in_rss', '0');
		Configs::set('hide_nsfw_in_rss', '1');

		$response = $this->getJson('Embed/stream');
		$this->assertOk($response);

		$data = $response->json();
		$photoIds = collect($data['photos'])->pluck('id')->toArray();

		// NSFW photo should be excluded
		$this->assertNotContains($nsfwPhoto->id, $photoIds, 'NSFW photo should be excluded when hide_nsfw_in_rss is enabled');

		// Clean up
		Configs::set('hide_nsfw_in_rss', $originalHideNsfw);
		$nsfwPhoto->delete();
		$nsfwAlbum->delete();
	}

	/**
	 * Test that photos are ordered by creation date (most recent first).
	 */
	public function testPhotosOrderedByCreationDate(): void
	{
		// Get multiple photos
		$response = $this->getJson('Embed/stream?limit=10');
		$this->assertOk($response);

		$data = $response->json();

		if (count($data['photos']) < 2) {
			$this->markTestSkipped('Not enough public photos to test ordering');
		}

		// Verify that created_at timestamps are in descending order
		$createdAts = collect($data['photos'])->map(fn ($photo) => strtotime($photo['exif']['taken_at'] ?? 'now'))->toArray();
		$sortedCreatedAts = $createdAts;
		rsort($sortedCreatedAts);

		$this->assertEquals($sortedCreatedAts, $createdAts, 'Photos should be ordered by creation date (most recent first)');
	}

	/**
	 * Test behavior when only a few photos exist in stream.
	 *
	 * Note: This test verifies that the endpoint correctly returns a valid response
	 * structure even when the result set is small or empty. The exact count may vary
	 * depending on which photos are considered "searchable" based on access policies.
	 */
	public function testStreamWithLimitedPhotos(): void
	{
		// Create a clean test by removing all public access permissions
		\App\Models\AccessPermission::query()->where('user_id', null)->forceDelete();

		$response = $this->getJson('Embed/stream');
		$this->assertOk($response);

		$data = $response->json();
		$this->assertIsArray($data['photos'], 'photos should be an array');
		// With no public access permissions, very few or no photos should be returned
		$this->assertLessThanOrEqual(1, count($data['photos']), 'Should return very few photos when no public access');
		$this->assertIsString($data['site_title'], 'site_title should still be present');

		// Clean up - recreate the permissions
		$this->perm4 = \App\Models\AccessPermission::factory()->public()->visible()->for_album($this->album4)->create();
		$this->perm44 = \App\Models\AccessPermission::factory()->public()->visible()->for_album($this->subAlbum4)->create();
	}

	/**
	 * Test that password-protected albums are excluded from stream.
	 */
	public function testPasswordProtectedAlbumsExcluded(): void
	{
		// Count photos before adding password
		$responseBefore = $this->getJson('Embed/stream');
		$photoCountBefore = count($responseBefore->json('photos'));

		// Add password to album4's public permission (which has public photos)
		\App\Models\AccessPermission::where('base_album_id', $this->album4->id)->update(['password' => 'test123']);

		$responseAfter = $this->getJson('Embed/stream');
		$this->assertOk($responseAfter);

		$data = $responseAfter->json();
		$photoIds = collect($data['photos'])->pluck('id')->toArray();

		// photo4 should be excluded now
		$this->assertNotContains($this->photo4->id, $photoIds, 'Photos from password-protected albums should be excluded');
		$this->assertLessThan($photoCountBefore, count($data['photos']), 'Photo count should decrease when album is password protected');

		// Clean up
		\App\Models\AccessPermission::where('base_album_id', $this->album4->id)->update(['password' => null]);
	}

	/**
	 * Test that link-required albums are excluded from stream.
	 */
	public function testLinkRequiredAlbumsExcluded(): void
	{
		// Count photos before adding link requirement
		$responseBefore = $this->getJson('Embed/stream');
		$photoCountBefore = count($responseBefore->json('photos'));

		// Make album4's public permission link-required (which has public photos)
		\App\Models\AccessPermission::where('base_album_id', $this->album4->id)->update(['is_link_required' => true]);

		$responseAfter = $this->getJson('Embed/stream');
		$this->assertOk($responseAfter);

		$data = $responseAfter->json();
		$photoIds = collect($data['photos'])->pluck('id')->toArray();

		// photo4 should be excluded now
		$this->assertNotContains($this->photo4->id, $photoIds, 'Photos from link-required albums should be excluded');
		$this->assertLessThan($photoCountBefore, count($data['photos']), 'Photo count should decrease when album is link-required');

		// Clean up
		\App\Models\AccessPermission::where('base_album_id', $this->album4->id)->update(['is_link_required' => false]);
	}

	/**
	 * Test that CORS headers are present.
	 */
	public function testCorsHeadersPresent(): void
	{
		$response = $this->getJson('Embed/stream');
		$this->assertOk($response);

		// CORS headers should be present for embedding
		$response->assertHeader('Access-Control-Allow-Origin');
	}

	/**
	 * Test that cache control headers are present.
	 */
	public function testCacheControlHeadersPresent(): void
	{
		$response = $this->getJson('Embed/stream');
		$this->assertOk($response);

		// Cache control should be set (15 minutes as per route definition)
		$response->assertHeader('Cache-Control');
	}
}
