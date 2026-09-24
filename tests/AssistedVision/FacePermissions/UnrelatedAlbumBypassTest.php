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

namespace Tests\AssistedVision\FacePermissions;

use App\Enum\FaceScanStatus;
use App\Jobs\DispatchFaceScanJob;
use App\Models\Configs;
use App\Models\Face;
use App\Models\Person;
use App\Models\Photo;
use App\Services\Image\FaceDetectionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Regression tests for GHSA-x6f7-qp5q-w37f.
 *
 * `POST /Face/batch` and `POST /FaceDetection/scan` accept an optional
 * `album_id`. Authorizing only that album let an attacker pass an unrelated
 * album they own while naming another user's face or photo by ID.
 *
 * Attacker: `userMayUpload2`, owner of `album2` (holding `photo2`) and of the
 * unrelated `subAlbum2` (holding `subPhoto2`).
 * Victim: `userNoUpload`, owner of `album3` and `photo3`; the attacker has no
 * permission on either.
 */
class UnrelatedAlbumBypassTest extends BaseApiWithDataTest
{
	private Person $person_source;
	private Person $person_destination;
	private Face $victim_face;
	private Face $attacker_face;

	public function setUp(): void
	{
		parent::setUp();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		Configs::set('ai_vision_face_permission_mode', 'privacy-preserving');

		$this->person_source = Person::factory()->with_name('Victim')->create();
		$this->person_destination = Person::factory()->with_name('Attacker')->create();
		$this->victim_face = Face::factory()->for_photo($this->photo3)->for_person($this->person_source)->without_crop()->create();
		$this->attacker_face = Face::factory()->for_photo($this->photo2)->without_crop()->create();
	}

	public function tearDown(): void
	{
		DB::table('face_suggestions')->delete();
		DB::table('faces')->delete();
		DB::table('persons')->delete();
		parent::tearDown();
	}

	// ── Face/batch ───────────────────────────────────────────────

	public function testBatchForeignFaceWithUnrelatedOwnedAlbumForbidden(): void
	{
		$response = $this->actingAs($this->userMayUpload2)->postJson('Face/batch', [
			'face_ids' => [$this->victim_face->id],
			'action' => 'assign',
			'person_id' => $this->person_destination->id,
			'album_id' => $this->album2->id,
		]);
		$this->assertForbidden($response);

		$this->victim_face->refresh();
		self::assertEquals($this->person_source->id, $this->victim_face->person_id);
	}

	public function testBatchForeignFaceWithoutAlbumForbidden(): void
	{
		$response = $this->actingAs($this->userMayUpload2)->postJson('Face/batch', [
			'face_ids' => [$this->victim_face->id],
			'action' => 'assign',
			'person_id' => $this->person_destination->id,
		]);
		$this->assertForbidden($response);

		$this->victim_face->refresh();
		self::assertEquals($this->person_source->id, $this->victim_face->person_id);
	}

	public function testBatchMixedOwnerFacesWithOwnAlbumForbidden(): void
	{
		$response = $this->actingAs($this->userMayUpload2)->postJson('Face/batch', [
			'face_ids' => [$this->attacker_face->id, $this->victim_face->id],
			'action' => 'assign',
			'person_id' => $this->person_destination->id,
			'album_id' => $this->album2->id,
		]);
		$this->assertForbidden($response);

		$this->victim_face->refresh();
		$this->attacker_face->refresh();
		self::assertEquals($this->person_source->id, $this->victim_face->person_id);
		self::assertNull($this->attacker_face->person_id);
	}

	public function testBatchOwnFaceWithForeignAlbumForbidden(): void
	{
		$response = $this->actingAs($this->userMayUpload2)->postJson('Face/batch', [
			'face_ids' => [$this->attacker_face->id],
			'action' => 'assign',
			'person_id' => $this->person_destination->id,
			'album_id' => $this->album3->id,
		]);
		$this->assertForbidden($response);

		$this->attacker_face->refresh();
		self::assertNull($this->attacker_face->person_id);
	}

	public function testBatchOwnFaceWithOwnButUnrelatedAlbumForbidden(): void
	{
		// `subAlbum2` is owned by the attacker, but `photo2` does not belong to it.
		$response = $this->actingAs($this->userMayUpload2)->postJson('Face/batch', [
			'face_ids' => [$this->attacker_face->id],
			'action' => 'assign',
			'person_id' => $this->person_destination->id,
			'album_id' => $this->subAlbum2->id,
		]);
		$this->assertForbidden($response);

		$this->attacker_face->refresh();
		self::assertNull($this->attacker_face->person_id);
	}

	public function testBatchOwnFaceWithMatchingAlbumAllowed(): void
	{
		$response = $this->actingAs($this->userMayUpload2)->postJson('Face/batch', [
			'face_ids' => [$this->attacker_face->id],
			'action' => 'assign',
			'person_id' => $this->person_destination->id,
			'album_id' => $this->album2->id,
		]);
		$this->assertOk($response);
		self::assertEquals(1, $response->json('affected_count'));

		$this->attacker_face->refresh();
		self::assertEquals($this->person_destination->id, $this->attacker_face->person_id);
	}

	public function testBatchUnassignForeignPhotoWithUnrelatedOwnedAlbumForbidden(): void
	{
		$response = $this->actingAs($this->userMayUpload2)->postJson('Face/batch', [
			'photo_ids' => [$this->photo3->id],
			'action' => 'unassign',
			'person_id' => $this->person_source->id,
			'album_id' => $this->album2->id,
		]);
		$this->assertForbidden($response);

		$this->victim_face->refresh();
		self::assertEquals($this->person_source->id, $this->victim_face->person_id);
	}

	// ── FaceDetection/scan ───────────────────────────────────────

	public function testScanForeignPhotoWithUnrelatedOwnedAlbumForbidden(): void
	{
		Queue::fake();

		$response = $this->actingAs($this->userMayUpload2)->postJson('FaceDetection/scan', [
			'photo_ids' => [$this->photo3->id],
			'force' => true,
			'album_id' => $this->album2->id,
		]);
		$this->assertForbidden($response);

		Queue::assertNothingPushed();
		$this->photo3->refresh();
		self::assertNull($this->photo3->face_scan_status);
	}

	public function testScanForeignPhotoWithoutAlbumForbidden(): void
	{
		Queue::fake();

		$response = $this->actingAs($this->userMayUpload2)->postJson('FaceDetection/scan', [
			'photo_ids' => [$this->photo3->id],
			'force' => true,
		]);
		$this->assertForbidden($response);

		Queue::assertNothingPushed();
		$this->photo3->refresh();
		self::assertNull($this->photo3->face_scan_status);
	}

	public function testScanOwnPhotoWithOwnButUnrelatedAlbumForbidden(): void
	{
		Queue::fake();

		$response = $this->actingAs($this->userMayUpload2)->postJson('FaceDetection/scan', [
			'photo_ids' => [$this->photo2->id],
			'force' => true,
			'album_id' => $this->subAlbum2->id,
		]);
		$this->assertForbidden($response);

		Queue::assertNothingPushed();
	}

	public function testScanOwnPhotoWithMatchingAlbumAllowed(): void
	{
		Queue::fake();

		$response = $this->actingAs($this->userMayUpload2)->postJson('FaceDetection/scan', [
			'photo_ids' => [$this->photo2->id],
			'force' => true,
			'album_id' => $this->album2->id,
		]);
		$this->assertNoContent($response);

		Queue::assertPushed(DispatchFaceScanJob::class, fn (DispatchFaceScanJob $job): bool => $job->photo_id === $this->photo2->id);
		$this->photo2->refresh();
		self::assertEquals(FaceScanStatus::PENDING, $this->photo2->face_scan_status);
	}

	public function testScanOwnAlbumAllowed(): void
	{
		Queue::fake();

		$response = $this->actingAs($this->userMayUpload2)->postJson('FaceDetection/scan', [
			'album_id' => $this->album2->id,
			'force' => true,
		]);
		$this->assertNoContent($response);

		Queue::assertPushed(DispatchFaceScanJob::class, fn (DispatchFaceScanJob $job): bool => $job->photo_id === $this->photo2->id);
	}

	// ── Service-level scoping ────────────────────────────────────

	public function testDispatchPhotosIntersectsIdsAndAlbum(): void
	{
		Queue::fake();

		$service = resolve(FaceDetectionService::class);
		$dispatched = $service->dispatchPhotos([$this->photo3->id], $this->album2->id, true);

		self::assertEquals(0, $dispatched);
		Queue::assertNothingPushed();

		$photo3 = Photo::find($this->photo3->id);
		self::assertNull($photo3->face_scan_status);
	}
}
