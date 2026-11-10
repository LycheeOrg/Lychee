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

namespace Tests\AssistedVision\NsfwClassification;

use App\Constants\PhotoAlbum;
use App\Enum\NsfwDetectionLabel;
use App\Enum\NsfwStatus;
use App\Models\Album;
use App\Models\NsfwDetection;
use App\Models\Photo;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequireSE;

/**
 * Feature 089 (FR-089-09, NFR-089-01, NFR-089-02, S-089-17, S-089-18): NSFW findings listing.
 */
class NsfwFindingsListTest extends BaseApiWithDataTest
{
	use RequireSE;

	private const PAGING = ['page' => 1, 'per_page' => 30];

	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();
	}

	public function tearDown(): void
	{
		DB::table('nsfw_detections')->delete();
		$this->resetSe();
		parent::tearDown();
	}

	public function testListsOnePhotoPerEntryLatestDetectionFirst(): void
	{
		$low = $this->addDetection($this->photo1, NsfwDetectionLabel::BELLY_EXPOSED, 0.41, is_sensitive: true);
		$this->addDetection($this->photo2, NsfwDetectionLabel::FEET_EXPOSED, 0.63, is_sensitive: true);
		$high = $this->addDetection($this->photo1, NsfwDetectionLabel::BUTTOCKS_EXPOSED, 0.82, is_review: true);
		Photo::query()->where('id', '=', $this->photo1->id)->update(['nsfw_status' => NsfwStatus::REVIEW->value, 'is_validated' => false]);
		$second_album = Album::factory()->as_root()->owned_by($this->admin)->create(['title' => 'AAA first']);
		$this->link($second_album, $this->photo1);

		$response = $this->actingAs($this->admin)->getJsonWithData('NsfwDetection/findings', self::PAGING);

		$this->assertOk($response);
		$response->assertJsonPath('total', 2);
		$response->assertJsonCount(2, 'photos');
		$response->assertJsonPath('photos.0.photo_id', $this->photo1->id);
		$response->assertJsonPath('photos.1.photo_id', $this->photo2->id);
		$response->assertJsonPath('photos.0.title', $this->photo1->title);
		$response->assertJsonPath('photos.0.owner_username', $this->photo1->owner->username);
		$response->assertJsonPath('photos.0.nsfw_status', 'review');
		$response->assertJsonPath('photos.0.is_validated', false);
		$response->assertJsonCount(2, 'photos.0.detections');
		$response->assertJsonPath('photos.0.detections.0.id', $high->id);
		$response->assertJsonPath('photos.0.detections.1.id', $low->id);
		$response->assertJsonCount(2, 'photos.0.albums');
		$response->assertJsonPath('photos.0.albums.0.id', $second_album->id);
		$response->assertJsonPath('photos.0.albums.1.id', $this->album1->id);
		self::assertNotNull($response->json('photos.0.scanned_at'));
		self::assertArrayHasKey('thumb_url', $response->json('photos.0'));
	}

	public function testPhotosWithoutDetectionsAreAbsent(): void
	{
		$response = $this->actingAs($this->admin)->getJsonWithData('NsfwDetection/findings', self::PAGING);

		$this->assertOk($response);
		$response->assertJsonPath('total', 0);
		$response->assertJsonCount(0, 'photos');
	}

	public function testPhotoWithoutAlbumHasEmptyAlbums(): void
	{
		$this->addDetection($this->photoUnsorted, NsfwDetectionLabel::FEET_EXPOSED, 0.5, is_sensitive: true);

		$response = $this->actingAs($this->admin)->getJsonWithData('NsfwDetection/findings', self::PAGING);

		$this->assertOk($response);
		$response->assertJsonPath('photos.0.photo_id', $this->photoUnsorted->id);
		$response->assertJsonCount(0, 'photos.0.albums');
	}

	public function testIsPaginatedByPhoto(): void
	{
		$this->addDetection($this->photo1, NsfwDetectionLabel::FEET_EXPOSED, 0.5, is_sensitive: true);
		$this->addDetection($this->photo1, NsfwDetectionLabel::BELLY_EXPOSED, 0.5, is_sensitive: true);
		$this->addDetection($this->photo2, NsfwDetectionLabel::FEET_EXPOSED, 0.5, is_sensitive: true);
		$this->addDetection($this->photo3, NsfwDetectionLabel::FEET_EXPOSED, 0.5, is_sensitive: true);

		$response = $this->actingAs($this->admin)->getJsonWithData('NsfwDetection/findings', ['page' => 2, 'per_page' => 2]);

		$this->assertOk($response);
		$response->assertJsonPath('total', 3);
		$response->assertJsonPath('last_page', 2);
		$response->assertJsonCount(1, 'photos');
		$response->assertJsonPath('photos.0.photo_id', $this->photo1->id);
		$response->assertJsonCount(2, 'photos.0.detections');
	}

	public function testAccessControl(): void
	{
		$this->assertUnauthorized($this->getJsonWithData('NsfwDetection/findings', self::PAGING));
		$this->assertForbidden($this->actingAs($this->userMayUpload1)->getJsonWithData('NsfwDetection/findings', self::PAGING));
	}

	public function testRequiresSupporterEdition(): void
	{
		$this->resetSe();

		$this->assertSupporterRequired($this->actingAs($this->admin)->getJsonWithData('NsfwDetection/findings', self::PAGING));
	}

	public function testValidatesPaging(): void
	{
		$this->assertUnprocessable($this->actingAs($this->admin)->getJsonWithData('NsfwDetection/findings', []));
		$this->assertUnprocessable($this->actingAs($this->admin)->getJsonWithData('NsfwDetection/findings', ['page' => 1, 'per_page' => 101]));
	}

	public function testQueryCountIsConstant(): void
	{
		$this->addDetection($this->photo1, NsfwDetectionLabel::FEET_EXPOSED, 0.5, is_sensitive: true);
		// Warm-up: the first request also loads the configs.
		$this->actingAs($this->admin)->getJsonWithData('NsfwDetection/findings', self::PAGING);
		$few = $this->countQueries();

		foreach ([$this->photo2, $this->photo3, $this->photo4, $this->subPhoto1, $this->photoUnsorted] as $photo) {
			$this->addDetection($photo, NsfwDetectionLabel::FEET_EXPOSED, 0.5, is_sensitive: true);
			$this->addDetection($photo, NsfwDetectionLabel::BELLY_EXPOSED, 0.6, is_review: true);
		}
		$extra_album = Album::factory()->as_root()->owned_by($this->admin)->create();
		$this->link($extra_album, $this->photo2, $this->photo3);
		$many = $this->countQueries();

		self::assertSame($few, $many);
	}

	private function addDetection(Photo $photo, NsfwDetectionLabel $label, float $confidence, bool $is_review = false, bool $is_sensitive = false): NsfwDetection
	{
		return NsfwDetection::query()->create([
			'photo_id' => $photo->id,
			'label' => $label,
			'confidence' => $confidence,
			'bbox_x' => 1,
			'bbox_y' => 2,
			'bbox_width' => 3,
			'bbox_height' => 4,
			'is_block' => false,
			'is_review' => $is_review,
			'is_sensitive' => $is_sensitive,
		]);
	}

	private function link(Album $album, Photo ...$photos): void
	{
		DB::table(PhotoAlbum::PHOTO_ALBUM)->insert(array_map(
			fn (Photo $photo) => ['album_id' => $album->id, 'photo_id' => $photo->id],
			$photos));
	}

	private function countQueries(): int
	{
		DB::flushQueryLog();
		DB::enableQueryLog();
		$this->assertOk($this->actingAs($this->admin)->getJsonWithData('NsfwDetection/findings', self::PAGING));
		$count = count(DB::getQueryLog());
		DB::disableQueryLog();

		return $count;
	}
}
