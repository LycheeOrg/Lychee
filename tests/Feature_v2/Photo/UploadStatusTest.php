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

namespace Tests\Feature_v2\Photo;

use App\DTO\UploadStatusEntry;
use App\Exceptions\PhotoRejectedException;
use App\Exceptions\PhotoSkippedException;
use App\Services\UploadStatusStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use function Safe\preg_match;
use Tests\Constants\TestConstants;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 116: `GET /api/v2/Photo::uploadStatus` (FR-116-06, FR-116-07, NFR-116-01, NFR-116-04).
 */
class UploadStatusTest extends BaseApiWithDataTest
{
	private const ID_A = 'UploadStatusTestIdAAAAAA';
	private const ID_B = 'UploadStatusTestIdBBBBBB';
	private const ID_C = 'UploadStatusTestIdCCCCCC';
	private const ID_D = 'UploadStatusTestIdDDDDDD';

	private UploadStatusStore $store;

	public function setUp(): void
	{
		parent::setUp();
		$this->store = resolve(UploadStatusStore::class);
	}

	/**
	 * S-116-01: a queued upload answers `processing` until the worker runs.
	 */
	public function testQueuedUploadIsProcessing(): void
	{
		Queue::fake();
		$response = $this->actingAs($this->userMayUpload1)->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE, album_id: $this->album1->id, file_name: 'night.jpg');
		$this->assertCreated($response);
		$expected_id = $response->json('expected_id');
		self::assertIsString($expected_id);

		$response = $this->getJsonWithData('Photo::uploadStatus', ['photo_ids' => [$expected_id]]);

		$this->assertOk($response);
		$response->assertExactJson([
			['expected_id' => $expected_id, 'status' => 'processing', 'photo_id' => null, 'message' => null],
		]);
	}

	/**
	 * S-116-12: no entry answers `unknown`.
	 */
	public function testUnknownIdIsUnknown(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Photo::uploadStatus', ['photo_ids' => [self::ID_A]]);

		$this->assertOk($response);
		$response->assertExactJson([
			['expected_id' => self::ID_A, 'status' => 'unknown', 'photo_id' => null, 'message' => null],
		]);
	}

	/**
	 * S-116-13, NFR-116-04: a user's entry answers only to that user.
	 */
	public function testUserEntryIsHiddenFromOthers(): void
	{
		$this->store->record(self::ID_A, UploadStatusEntry::ofPhoto($this->userMayUpload1->id, $this->photo1->id, is_duplicate: false));

		$this->actingAs($this->userMayUpload1)->getJsonWithData('Photo::uploadStatus', ['photo_ids' => [self::ID_A]])
			->assertJsonPath('0.status', 'completed')
			->assertJsonPath('0.photo_id', $this->photo1->id);

		$this->actingAs($this->userMayUpload2)->getJsonWithData('Photo::uploadStatus', ['photo_ids' => [self::ID_A]])
			->assertExactJson([['expected_id' => self::ID_A, 'status' => 'unknown', 'photo_id' => null, 'message' => null]]);
	}

	public function testUserEntryIsHiddenFromGuests(): void
	{
		$this->store->record(self::ID_A, UploadStatusEntry::processing($this->userMayUpload1->id));

		$this->getJsonWithData('Photo::uploadStatus', ['photo_ids' => [self::ID_A]])
			->assertOk()
			->assertJsonPath('0.status', 'unknown');
	}

	/**
	 * S-116-13: a guest upload's entry answers to anyone holding its ID.
	 */
	public function testGuestEntryIsReadableByAnyone(): void
	{
		$this->store->record(self::ID_A, UploadStatusEntry::processing(null));

		$this->getJsonWithData('Photo::uploadStatus', ['photo_ids' => [self::ID_A]])
			->assertOk()
			->assertJsonPath('0.status', 'processing');
		$this->actingAs($this->userMayUpload2)->getJsonWithData('Photo::uploadStatus', ['photo_ids' => [self::ID_A]])
			->assertOk()
			->assertJsonPath('0.status', 'processing');
	}

	/**
	 * FR-116-07, S-116-08: the duplicate's ID is given even when the caller cannot see that photo;
	 * the uploader has the file already and photo routes apply their own policy.
	 */
	public function testDuplicateIdOfAnotherUsersPhotoIsGiven(): void
	{
		$this->store->record(self::ID_A, UploadStatusEntry::ofThrowable($this->userMayUpload1->id, new PhotoRejectedException(photo_id: $this->photo2->id)));

		$this->actingAs($this->userMayUpload1)->getJsonWithData('Photo::uploadStatus', ['photo_ids' => [self::ID_A]])
			->assertOk()
			->assertExactJson([['expected_id' => self::ID_A, 'status' => 'skipped', 'photo_id' => $this->photo2->id, 'message' => PhotoRejectedException::DEFAULT_MESSAGE]]);
	}

	public function testVisibleDuplicateIdIsGiven(): void
	{
		$this->store->record(self::ID_A, UploadStatusEntry::ofThrowable($this->userMayUpload1->id, new PhotoSkippedException(photo_id: $this->photo1->id)));

		$this->actingAs($this->userMayUpload1)->getJsonWithData('Photo::uploadStatus', ['photo_ids' => [self::ID_A]])
			->assertOk()
			->assertExactJson([['expected_id' => self::ID_A, 'status' => 'skipped', 'photo_id' => $this->photo1->id, 'message' => PhotoSkippedException::DEFAULT_MESSAGE]]);
	}

	public function testDeletedPhotoIdIsWithheld(): void
	{
		$this->store->record(self::ID_A, UploadStatusEntry::ofPhoto($this->userMayUpload1->id, 'DeletedPhotoIdDeletedPho', is_duplicate: true));

		$this->actingAs($this->userMayUpload1)->getJsonWithData('Photo::uploadStatus', ['photo_ids' => [self::ID_A]])
			->assertOk()
			->assertExactJson([['expected_id' => self::ID_A, 'status' => 'duplicate', 'photo_id' => null, 'message' => null]]);
	}

	/**
	 * S-116-18, NFR-116-01: a batch answers in request order with one photo query.
	 */
	public function testBatchAnswersInRequestOrder(): void
	{
		$this->store->record(self::ID_A, UploadStatusEntry::ofPhoto($this->userMayUpload1->id, $this->photo1->id, is_duplicate: false));
		$this->store->record(self::ID_B, UploadStatusEntry::processing($this->userMayUpload1->id));
		$this->store->record(self::ID_C, UploadStatusEntry::ofPhoto($this->userMayUpload1->id, $this->photo1b->id, is_duplicate: true));
		$this->actingAs($this->userMayUpload1);

		DB::enableQueryLog();
		$response = $this->getJsonWithData('Photo::uploadStatus', ['photo_ids' => [self::ID_B, 'UploadStatusTestIdNoneXX', self::ID_A, self::ID_C, self::ID_A]]);
		$photo_queries = array_filter(DB::getQueryLog(), fn (array $q) => preg_match('/from\s+["`]?photos["`]?\s/i', $q['query']) === 1);
		DB::disableQueryLog();

		$this->assertOk($response);
		$response->assertExactJson([
			['expected_id' => self::ID_B, 'status' => 'processing', 'photo_id' => null, 'message' => null],
			['expected_id' => 'UploadStatusTestIdNoneXX', 'status' => 'unknown', 'photo_id' => null, 'message' => null],
			['expected_id' => self::ID_A, 'status' => 'completed', 'photo_id' => $this->photo1->id, 'message' => null],
			['expected_id' => self::ID_C, 'status' => 'duplicate', 'photo_id' => $this->photo1b->id, 'message' => null],
			['expected_id' => self::ID_A, 'status' => 'completed', 'photo_id' => $this->photo1->id, 'message' => null],
		]);
		self::assertCount(1, $photo_queries, 'Expected one photo query for the whole batch.');
	}

	/**
	 * FR-116-07, NFR-116-01: photos of other users cost no extra query.
	 * photo1: album1 shared with userMayUpload2 (perm1); photo4: public album4; photo3: not accessible; ID_D: deleted.
	 */
	public function testPhotoIdsOfOtherUsersAreGivenInOneQuery(): void
	{
		$uploader = $this->userMayUpload2->id;
		$this->store->record(self::ID_A, UploadStatusEntry::ofPhoto($uploader, $this->photo1->id, is_duplicate: true));
		$this->store->record(self::ID_B, UploadStatusEntry::ofPhoto($uploader, $this->photo4->id, is_duplicate: true));
		$this->store->record(self::ID_C, UploadStatusEntry::ofPhoto($uploader, $this->photo3->id, is_duplicate: true));
		$this->store->record(self::ID_D, UploadStatusEntry::ofPhoto($uploader, 'DeletedPhotoIdDeletedPho', is_duplicate: true));
		$this->actingAs($this->userMayUpload2);

		// Warm-up, so that per-process caches do not skew the counts below.
		$this->getJsonWithData('Photo::uploadStatus', ['photo_ids' => [self::ID_A]]);

		DB::enableQueryLog();
		$this->getJsonWithData('Photo::uploadStatus', ['photo_ids' => [self::ID_A]]);
		$single = count(DB::getQueryLog());
		DB::flushQueryLog();
		$response = $this->getJsonWithData('Photo::uploadStatus', ['photo_ids' => [self::ID_A, self::ID_B, self::ID_C, self::ID_D]]);
		$batch = count(DB::getQueryLog());
		DB::disableQueryLog();

		$this->assertOk($response);
		$response->assertExactJson([
			['expected_id' => self::ID_A, 'status' => 'duplicate', 'photo_id' => $this->photo1->id, 'message' => null],
			['expected_id' => self::ID_B, 'status' => 'duplicate', 'photo_id' => $this->photo4->id, 'message' => null],
			['expected_id' => self::ID_C, 'status' => 'duplicate', 'photo_id' => $this->photo3->id, 'message' => null],
			['expected_id' => self::ID_D, 'status' => 'duplicate', 'photo_id' => null, 'message' => null],
		]);
		self::assertSame($single, $batch, 'The query count must not grow with the number of photos the caller does not own.');
	}

	/**
	 * API clients send the IDs as a query string.
	 */
	public function testQueryStringForm(): void
	{
		$this->store->record(self::ID_A, UploadStatusEntry::processing($this->userMayUpload1->id));
		$this->store->record(self::ID_B, UploadStatusEntry::processing($this->userMayUpload1->id));

		$response = $this->actingAs($this->userMayUpload1)->withCredentials()
			->getJson('Photo::uploadStatus?photo_ids[]=' . self::ID_A . '&photo_ids[]=' . self::ID_B);

		$this->assertOk($response);
		$response->assertJsonPath('0.expected_id', self::ID_A);
		$response->assertJsonPath('1.expected_id', self::ID_B);
	}

	/**
	 * S-116-16: malformed requests.
	 */
	public function testMalformedIdIsRejected(): void
	{
		$this->assertUnprocessable($this->getJsonWithData('Photo::uploadStatus', ['photo_ids' => ['short']]));
	}

	public function testEmptyListIsRejected(): void
	{
		$this->assertUnprocessable($this->getJsonWithData('Photo::uploadStatus', ['photo_ids' => []]));
		$this->assertUnprocessable($this->getJsonWithData('Photo::uploadStatus', []));
	}

	public function testMoreThanOneHundredIdsAreRejected(): void
	{
		$ids = array_map(fn (int $i) => sprintf('UploadStatusTestId%06d', $i), range(1, 101));

		$this->assertUnprocessable($this->getJsonWithData('Photo::uploadStatus', ['photo_ids' => $ids]));
		$this->assertOk($this->getJsonWithData('Photo::uploadStatus', ['photo_ids' => array_slice($ids, 0, 100)]));
	}
}
