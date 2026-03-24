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

namespace Tests\Feature_v2\AiVision;

use App\Enum\FaceScanStatus;
use App\Models\Configs;
use App\Models\Face;
use App\Models\Person;
use App\Models\Photo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class FaceDetectionTest extends BaseApiWithDataTest
{
	private string $api_key;

	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		Configs::set('ai_vision_face_permission_mode', 'public');

		$this->api_key = 'test-api-key-12345';
		config(['features.ai-vision.face-api-key' => $this->api_key]);
	}

	public function tearDown(): void
	{
		DB::table('face_suggestions')->delete();
		DB::table('faces')->delete();
		DB::table('persons')->delete();
		$this->resetSe();
		parent::tearDown();
	}

	// ── SCAN TRIGGER ────────────────────────────────────────────

	public function testScanPhotos(): void
	{
		Queue::fake();

		$response = $this->actingAs($this->userMayUpload1)->postJson('FaceDetection/scan', [
			'photo_ids' => [$this->photo1->id],
		]);
		$this->assertStatus($response, 202);
	}

	public function testScanAlbum(): void
	{
		Queue::fake();

		$response = $this->actingAs($this->userMayUpload1)->postJson('FaceDetection/scan', [
			'album_id' => $this->album1->id,
		]);
		$this->assertStatus($response, 202);
	}

	public function testScanValidationRequiresPhotoOrAlbum(): void
	{
		$response = $this->actingAs($this->admin)->postJson('FaceDetection/scan', []);
		$this->assertUnprocessable($response);
	}

	public function testScanAsGuestUnauthorized(): void
	{
		$response = $this->postJson('FaceDetection/scan', [
			'photo_ids' => [$this->photo1->id],
		]);
		$this->assertUnauthorized($response);
	}

	// ── RESULTS CALLBACK ────────────────────────────────────────

	public function testResultsSuccess(): void
	{
		// Set photo to pending status
		Photo::where('id', $this->photo1->id)->update(['face_scan_status' => FaceScanStatus::PENDING->value]);

		$response = $this->postJson('FaceDetection/results', [
			'photo_id' => $this->photo1->id,
			'status' => 'success',
			'faces' => [
				[
					'x' => 0.1,
					'y' => 0.2,
					'width' => 0.15,
					'height' => 0.2,
					'confidence' => 0.95,
					'embedding_id' => 'emb_001',
					'crop' => base64_encode('fake-crop-data'),
				],
			],
		], ['X-API-Key' => $this->api_key]);

		$this->assertOk($response);
		self::assertCount(1, $response->json('faces'));
		self::assertEquals('emb_001', $response->json('faces.0.embedding_id'));

		// Verify face was created
		$faces = Face::where('photo_id', $this->photo1->id)->get();
		self::assertCount(1, $faces);
		self::assertEqualsWithDelta(0.95, $faces->first()->confidence, 0.001);

		// Verify photo status updated
		$this->photo1->refresh();
		self::assertEquals(FaceScanStatus::COMPLETED, $this->photo1->face_scan_status);
	}

	public function testResultsError(): void
	{
		Photo::where('id', $this->photo1->id)->update(['face_scan_status' => FaceScanStatus::PENDING->value]);

		$response = $this->postJson('FaceDetection/results', [
			'photo_id' => $this->photo1->id,
			'status' => 'error',
			'message' => 'No face found',
		], ['X-API-Key' => $this->api_key]);

		$this->assertOk($response);

		// Photo should be marked as failed
		$this->photo1->refresh();
		self::assertEquals(FaceScanStatus::FAILED, $this->photo1->face_scan_status);
	}

	public function testResultsInvalidApiKey(): void
	{
		$response = $this->postJson('FaceDetection/results', [
			'photo_id' => $this->photo1->id,
			'status' => 'success',
			'faces' => [],
		], ['X-API-Key' => 'wrong-key']);

		$this->assertForbidden($response);
	}

	public function testResultsInvalidPhotoId(): void
	{
		$response = $this->postJson('FaceDetection/results', [
			'photo_id' => 'nonexistent_photo_id',
			'status' => 'success',
			'faces' => [],
		], ['X-API-Key' => $this->api_key]);

		$this->assertUnprocessable($response);
	}

	public function testResultsRescanPreservesPersonId(): void
	{
		// Create existing face with person
		$person = Person::factory()->with_name('Alice')->create();
		$existing_face = Face::factory()->for_photo($this->photo1)->for_person($person)->create([
			'x' => 0.1,
			'y' => 0.2,
			'width' => 0.15,
			'height' => 0.2,
		]);

		Photo::where('id', $this->photo1->id)->update(['face_scan_status' => FaceScanStatus::PENDING->value]);

		// Re-scan with a face at the same location (high IoU)
		$response = $this->postJson('FaceDetection/results', [
			'photo_id' => $this->photo1->id,
			'status' => 'success',
			'faces' => [
				[
					'x' => 0.1,
					'y' => 0.2,
					'width' => 0.15,
					'height' => 0.2,
					'confidence' => 0.98,
					'embedding_id' => 'emb_rescan_001',
				],
			],
		], ['X-API-Key' => $this->api_key]);

		$this->assertOk($response);

		// New face should have inherited the person_id
		$new_face = Face::where('photo_id', $this->photo1->id)->first();
		self::assertNotNull($new_face);
		self::assertEquals($person->id, $new_face->person_id);
	}

	// ── BULK SCAN ───────────────────────────────────────────────

	public function testBulkScanAsAdmin(): void
	{
		Queue::fake();

		$response = $this->actingAs($this->admin)->postJson('FaceDetection/bulk-scan');
		$this->assertStatus($response, 202);
	}

	public function testBulkScanAsUserForbidden(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('FaceDetection/bulk-scan');
		$this->assertForbidden($response);
	}

	// ── CLUSTER RESULTS ─────────────────────────────────────────

	public function testClusterResults(): void
	{
		$face1 = Face::factory()->for_photo($this->photo1)->create();
		$face2 = Face::factory()->for_photo($this->photo1)->create();

		$response = $this->postJson('FaceDetection/cluster-results', [
			'labels' => [
				['face_id' => $face1->id, 'cluster_label' => 1],
				['face_id' => $face2->id, 'cluster_label' => 1],
			],
			'suggestions' => [
				['face_id' => $face1->id, 'suggested_face_id' => $face2->id, 'confidence' => 0.9],
			],
		], ['X-API-Key' => $this->api_key]);

		$this->assertStatus($response, 202);

		// Verify cluster labels
		$face1->refresh();
		$face2->refresh();
		self::assertEquals(1, $face1->cluster_label);
		self::assertEquals(1, $face2->cluster_label);
	}

	public function testClusterResultsInvalidApiKey(): void
	{
		$response = $this->postJson('FaceDetection/cluster-results', [
			'labels' => [],
			'suggestions' => [],
		], ['X-API-Key' => 'wrong-key']);

		$this->assertForbidden($response);
	}
}
