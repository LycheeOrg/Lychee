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

namespace Tests\Feature_v2\Album;

use App\Models\Configs;
use App\Models\Photo;
use App\SmartAlbums\BestPicturesAlbum;
use App\SmartAlbums\FiveStarsAlbum;
use App\SmartAlbums\FourStarsAlbum;
use App\SmartAlbums\OneStarAlbum;
use App\SmartAlbums\ThreeStarsAlbum;
use App\SmartAlbums\TwoStarsAlbum;
use App\SmartAlbums\UnratedAlbum;
use LycheeVerify\Contract\VerifyInterface;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequireSupport;

/**
 * Tests for rating-based smart album filtering (Feature 009).
 *
 * T-009-15: UnratedAlbum
 * T-009-17: OneStarAlbum
 * T-009-19: TwoStarsAlbum
 * T-009-21: ThreeStarsAlbum
 * T-009-23: FourStarsAlbum
 * T-009-25: FiveStarsAlbum
 * T-009-27,28,29: BestPicturesAlbum
 */
class RatingSmartAlbumsTest extends BaseApiWithDataTest
{
	use RequireSupport;

	protected Photo $ratedPhoto1;
	protected Photo $ratedPhoto2;
	protected Photo $ratedPhoto3;
	protected Photo $ratedPhoto4;
	protected Photo $ratedPhoto5;

	public function setUp(): void
	{
		parent::setUp();

		// Create photos with various rating_avg values for testing
		// Using direct database manipulation for rating_avg as we need specific test values

		// Unrated photo - use existing photo1 (rating_avg = NULL)
		$this->photo1->rating_avg = null;
		$this->photo1->save();

		// 1-star rating (1.0 <= rating < 2.0)
		$this->ratedPhoto1 = Photo::factory()->owned_by($this->userMayUpload1)->in($this->album1)->create();
		$this->ratedPhoto1->rating_avg = '1.5000';
		$this->ratedPhoto1->save();

		// 2-star rating (2.0 <= rating < 3.0)
		$this->ratedPhoto2 = Photo::factory()->owned_by($this->userMayUpload1)->in($this->album1)->create();
		$this->ratedPhoto2->rating_avg = '2.5000';
		$this->ratedPhoto2->save();

		// 3-star rating (3.0 <= rating, includes 3, 4, 5)
		$this->ratedPhoto3 = Photo::factory()->owned_by($this->userMayUpload1)->in($this->album1)->create();
		$this->ratedPhoto3->rating_avg = '3.0000';
		$this->ratedPhoto3->save();

		// 4-star rating (4.0 <= rating, includes 4, 5)
		$this->ratedPhoto4 = Photo::factory()->owned_by($this->userMayUpload1)->in($this->album1)->create();
		$this->ratedPhoto4->rating_avg = '4.5000';
		$this->ratedPhoto4->save();

		// 5-star rating (5.0 exactly)
		$this->ratedPhoto5 = Photo::factory()->owned_by($this->userMayUpload1)->in($this->album1)->create();
		$this->ratedPhoto5->rating_avg = '5.0000';
		$this->ratedPhoto5->save();

		// Override SA visibility for easier testing
		Configs::set('SA_override_visibility', '1');
	}

	/**
	 * T-009-15: Test UnratedAlbum contains only photos with no ratings.
	 */
	public function testUnratedAlbumFiltering(): void
	{
		$album = UnratedAlbum::getInstance();
		$photos = $album->photos()->get();

		// Should contain photo1 (and other existing photos with null rating)
		$this->assertTrue($photos->contains('id', $this->photo1->id));

		// Should NOT contain any rated photos
		$this->assertFalse($photos->contains('id', $this->ratedPhoto1->id));
		$this->assertFalse($photos->contains('id', $this->ratedPhoto2->id));
		$this->assertFalse($photos->contains('id', $this->ratedPhoto3->id));
		$this->assertFalse($photos->contains('id', $this->ratedPhoto4->id));
		$this->assertFalse($photos->contains('id', $this->ratedPhoto5->id));
	}

	/**
	 * T-009-17: Test OneStarAlbum contains only photos with 1.0 <= rating_avg < 2.0.
	 */
	public function testOneStarAlbumFiltering(): void
	{
		$album = OneStarAlbum::getInstance();
		$photos = $album->photos()->get();

		// Should contain ratedPhoto1 (1.5)
		$this->assertTrue($photos->contains('id', $this->ratedPhoto1->id));

		// Should NOT contain photos outside range
		$this->assertFalse($photos->contains('id', $this->photo1->id)); // unrated
		$this->assertFalse($photos->contains('id', $this->ratedPhoto2->id)); // 2.5
		$this->assertFalse($photos->contains('id', $this->ratedPhoto3->id)); // 3.0
	}

	/**
	 * T-009-17: Test OneStarAlbum boundary at exactly 2.0 (should be excluded).
	 */
	public function testOneStarAlbumBoundaryAt2(): void
	{
		// Create a photo with exactly 2.0 rating
		$boundaryPhoto = Photo::factory()->owned_by($this->userMayUpload1)->in($this->album1)->create();
		$boundaryPhoto->rating_avg = '2.0000';
		$boundaryPhoto->save();

		$album = OneStarAlbum::getInstance();
		$photos = $album->photos()->get();

		// 2.0 should be EXCLUDED from 1-star album
		$this->assertFalse($photos->contains('id', $boundaryPhoto->id));

		// Clean up
		$boundaryPhoto->delete();
	}

	/**
	 * T-009-17: Test OneStarAlbum boundary at exactly 1.0 (should be included).
	 */
	public function testOneStarAlbumBoundaryAt1(): void
	{
		// Create a photo with exactly 1.0 rating
		$boundaryPhoto = Photo::factory()->owned_by($this->userMayUpload1)->in($this->album1)->create();
		$boundaryPhoto->rating_avg = '1.0000';
		$boundaryPhoto->save();

		$album = OneStarAlbum::getInstance();
		$photos = $album->photos()->get();

		// 1.0 should be INCLUDED in 1-star album
		$this->assertTrue($photos->contains('id', $boundaryPhoto->id));

		// Clean up
		$boundaryPhoto->delete();
	}

	/**
	 * T-009-19: Test TwoStarsAlbum contains only photos with 2.0 <= rating_avg < 3.0.
	 */
	public function testTwoStarsAlbumFiltering(): void
	{
		$album = TwoStarsAlbum::getInstance();
		$photos = $album->photos()->get();

		// Should contain ratedPhoto2 (2.5)
		$this->assertTrue($photos->contains('id', $this->ratedPhoto2->id));

		// Should NOT contain photos outside range
		$this->assertFalse($photos->contains('id', $this->ratedPhoto1->id)); // 1.5
		$this->assertFalse($photos->contains('id', $this->ratedPhoto3->id)); // 3.0
	}

	/**
	 * T-009-21: Test ThreeStarsAlbum contains photos with rating_avg >= 3.0 (includes 4★, 5★).
	 */
	public function testThreeStarsAlbumFiltering(): void
	{
		$album = ThreeStarsAlbum::getInstance();
		$photos = $album->photos()->get();

		// Should contain all photos >= 3.0
		$this->assertTrue($photos->contains('id', $this->ratedPhoto3->id)); // 3.0
		$this->assertTrue($photos->contains('id', $this->ratedPhoto4->id)); // 4.5
		$this->assertTrue($photos->contains('id', $this->ratedPhoto5->id)); // 5.0

		// Should NOT contain photos below 3.0
		$this->assertFalse($photos->contains('id', $this->ratedPhoto1->id)); // 1.5
		$this->assertFalse($photos->contains('id', $this->ratedPhoto2->id)); // 2.5
		$this->assertFalse($photos->contains('id', $this->photo1->id)); // unrated
	}

	/**
	 * T-009-21: Test ThreeStarsAlbum boundary at exactly 3.0 (should be included).
	 */
	public function testThreeStarsAlbumBoundaryAt3(): void
	{
		// ratedPhoto3 has exactly 3.0, should be included
		$album = ThreeStarsAlbum::getInstance();
		$photos = $album->photos()->get();

		$this->assertTrue($photos->contains('id', $this->ratedPhoto3->id));
	}

	/**
	 * T-009-23: Test FourStarsAlbum contains photos with rating_avg >= 4.0.
	 */
	public function testFourStarsAlbumFiltering(): void
	{
		$album = FourStarsAlbum::getInstance();
		$photos = $album->photos()->get();

		// Should contain photos >= 4.0
		$this->assertTrue($photos->contains('id', $this->ratedPhoto4->id)); // 4.5
		$this->assertTrue($photos->contains('id', $this->ratedPhoto5->id)); // 5.0

		// Should NOT contain photos below 4.0
		$this->assertFalse($photos->contains('id', $this->ratedPhoto3->id)); // 3.0
		$this->assertFalse($photos->contains('id', $this->ratedPhoto2->id)); // 2.5
		$this->assertFalse($photos->contains('id', $this->ratedPhoto1->id)); // 1.5
	}

	/**
	 * T-009-25: Test FiveStarsAlbum contains only photos with perfect 5.0 rating.
	 */
	public function testFiveStarsAlbumFiltering(): void
	{
		$album = FiveStarsAlbum::getInstance();
		$photos = $album->photos()->get();

		// Should contain only ratedPhoto5 (5.0)
		$this->assertTrue($photos->contains('id', $this->ratedPhoto5->id));

		// Should NOT contain any other photos
		$this->assertFalse($photos->contains('id', $this->ratedPhoto4->id)); // 4.5
		$this->assertFalse($photos->contains('id', $this->ratedPhoto3->id)); // 3.0
	}

	/**
	 * T-009-25: Test FiveStarsAlbum excludes 4.9999 rating.
	 */
	public function testFiveStarsAlbumExcludesNearPerfect(): void
	{
		$nearPerfectPhoto = Photo::factory()->owned_by($this->userMayUpload1)->in($this->album1)->create();
		$nearPerfectPhoto->rating_avg = '4.9999';
		$nearPerfectPhoto->save();

		$album = FiveStarsAlbum::getInstance();
		$photos = $album->photos()->get();

		// 4.9999 should NOT be in 5-star album
		$this->assertFalse($photos->contains('id', $nearPerfectPhoto->id));

		// Clean up
		$nearPerfectPhoto->delete();
	}

	/**
	 * T-009-27: Test BestPicturesAlbum returns top N photos by rating (requires Lychee SE).
	 */
	public function testBestPicturesAlbumBasicCutoff(): void
	{
		// Register supporter verifier
		$this->app->instance(VerifyInterface::class, $this->getSupporter());

		// Set best_pictures_count to 2 (only top 2)
		Configs::set('best_pictures_count', '2');

		$album = BestPicturesAlbum::getInstance();
		// Use get_photos() to trigger the getPhotosAttribute() override with cutoff logic
		$photos = $album->get_photos();

		// Should return top 2 photos by rating: 5.0, 4.5
		$this->assertTrue($photos->contains('id', $this->ratedPhoto5->id)); // 5.0
		$this->assertTrue($photos->contains('id', $this->ratedPhoto4->id)); // 4.5

		// Should NOT contain lower rated photos
		$this->assertFalse($photos->contains('id', $this->ratedPhoto3->id)); // 3.0 (outside top 2)
		$this->assertFalse($photos->contains('id', $this->ratedPhoto2->id)); // 2.5
		$this->assertFalse($photos->contains('id', $this->ratedPhoto1->id)); // 1.5
		$this->assertFalse($photos->contains('id', $this->photo1->id)); // unrated
	}

	/**
	 * T-009-28: Test BestPicturesAlbum includes ties (may show > N photos).
	 */
	public function testBestPicturesAlbumTieInclusion(): void
	{
		// Register supporter verifier
		$this->app->instance(VerifyInterface::class, $this->getSupporter());

		// Create another 3.0 rated photo to create a tie
		$tiePhoto = Photo::factory()->owned_by($this->userMayUpload1)->in($this->album1)->create();
		$tiePhoto->rating_avg = '3.0000';
		$tiePhoto->save();

		// Set best_pictures_count to 3
		Configs::set('best_pictures_count', '3');

		$album = BestPicturesAlbum::getInstance();
		$photos = $album->photos()->get();

		// Should include both 3.0 photos due to tie at cutoff
		// Top 3 by rating: 5.0 (1), 4.5 (2), 3.0 (3) - but 3.0 has a tie
		$this->assertTrue($photos->contains('id', $this->ratedPhoto5->id)); // 5.0
		$this->assertTrue($photos->contains('id', $this->ratedPhoto4->id)); // 4.5
		$this->assertTrue($photos->contains('id', $this->ratedPhoto3->id)); // 3.0
		$this->assertTrue($photos->contains('id', $tiePhoto->id)); // 3.0 (tie)

		// Total should be 4 (> N=3) due to tie inclusion
		$this->assertGreaterThanOrEqual(4, $photos->count());

		// Clean up
		$tiePhoto->delete();
	}

	/**
	 * T-009-29: Test BestPicturesAlbum requires Lychee SE.
	 */
	public function testBestPicturesAlbumRequiresSE(): void
	{
		// Register free verifier (no SE)
		$this->app->instance(VerifyInterface::class, $this->getFree());

		// Verify that the smart album config returns disabled status
		Configs::set('enable_best_pictures', '1');

		// Get the response via API (checking if album is visible)
		$response = $this->actingAs($this->admin)->getJson('Albums');
		$this->assertOk($response);

		$data = $response->json();

		// Best Pictures should NOT be in the list of smart albums when SE is not active
		$smartAlbums = collect($data['resource']['smart_albums'] ?? []);
		$bestPictures = $smartAlbums->firstWhere('id', 'best_pictures');

		$this->assertNull($bestPictures, 'Best Pictures album should not appear without Lychee SE');
	}

	/**
	 * Test that unrated photos are excluded from all rating tier albums.
	 */
	public function testUnratedPhotosExcludedFromRatingTiers(): void
	{
		// Verify unrated photo1 is not in any rating tier
		$oneStarPhotos = OneStarAlbum::getInstance()->photos()->get();
		$twoStarsPhotos = TwoStarsAlbum::getInstance()->photos()->get();
		$threeStarsPhotos = ThreeStarsAlbum::getInstance()->photos()->get();
		$fourStarsPhotos = FourStarsAlbum::getInstance()->photos()->get();
		$fiveStarsPhotos = FiveStarsAlbum::getInstance()->photos()->get();

		$this->assertFalse($oneStarPhotos->contains('id', $this->photo1->id));
		$this->assertFalse($twoStarsPhotos->contains('id', $this->photo1->id));
		$this->assertFalse($threeStarsPhotos->contains('id', $this->photo1->id));
		$this->assertFalse($fourStarsPhotos->contains('id', $this->photo1->id));
		$this->assertFalse($fiveStarsPhotos->contains('id', $this->photo1->id));
	}

	/**
	 * Test photo appears in exactly one bucket album (1★ or 2★).
	 */
	public function testPhotoAppearsInExactlyOneBucketAlbum(): void
	{
		// ratedPhoto1 (1.5) should be in 1-star only
		$oneStarPhotos = OneStarAlbum::getInstance()->photos()->get();
		$twoStarsPhotos = TwoStarsAlbum::getInstance()->photos()->get();

		$this->assertTrue($oneStarPhotos->contains('id', $this->ratedPhoto1->id));
		$this->assertFalse($twoStarsPhotos->contains('id', $this->ratedPhoto1->id));

		// ratedPhoto2 (2.5) should be in 2-star only
		$this->assertFalse($oneStarPhotos->contains('id', $this->ratedPhoto2->id));
		$this->assertTrue($twoStarsPhotos->contains('id', $this->ratedPhoto2->id));
	}

	/**
	 * Test photo can appear in multiple threshold albums (3★+, 4★+, 5★).
	 */
	public function testPhotoCanAppearInMultipleThresholdAlbums(): void
	{
		// ratedPhoto5 (5.0) should be in 3★+, 4★+, AND 5★
		$threeStarsPhotos = ThreeStarsAlbum::getInstance()->photos()->get();
		$fourStarsPhotos = FourStarsAlbum::getInstance()->photos()->get();
		$fiveStarsPhotos = FiveStarsAlbum::getInstance()->photos()->get();

		$this->assertTrue($threeStarsPhotos->contains('id', $this->ratedPhoto5->id));
		$this->assertTrue($fourStarsPhotos->contains('id', $this->ratedPhoto5->id));
		$this->assertTrue($fiveStarsPhotos->contains('id', $this->ratedPhoto5->id));
	}
}
