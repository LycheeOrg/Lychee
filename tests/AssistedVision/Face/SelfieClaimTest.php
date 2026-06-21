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

namespace Tests\AssistedVision\Face;

use App\Models\Configs;
use App\Models\Face;
use App\Models\Person;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class SelfieClaimTest extends BaseApiWithDataTest
{
	private Person $person1;

	public function setUp(): void
	{
		parent::setUp();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		Configs::set('ai_vision_face_permission_mode', 'public');
		Configs::set('ai_vision_face_allow_user_claim', '1');
		Configs::set('ai_vision_face_selfie_confidence_threshold', '0.8');

		config(['features.ai-vision-service.face-url' => 'http://fake-vision-service:8000']);
		config(['features.ai-vision-service.face-api-key' => 'test-api-key']);

		$this->person1 = Person::factory()->with_name('Alice')->create();
	}

	public function tearDown(): void
	{
		DB::table('face_suggestions')->delete();
		DB::table('faces')->delete();
		DB::table('persons')->delete();
		parent::tearDown();
	}

	public function testSelfieClaimSuccess(): void
	{
		$face = Face::factory()->for_photo($this->photo1)->for_person($this->person1)->create();

		Http::fake([
			'fake-vision-service:8000/match' => Http::response([
				'matches' => [
					[
						'lychee_face_id' => $face->id,
						'person_id' => $this->person1->id,
						'confidence' => 0.95,
					],
				],
			], 200),
		]);

		$selfie = UploadedFile::fake()->image('selfie.jpg', 200, 200);

		$response = $this->actingAs($this->userMayUpload1)->post(
			self::API_PREFIX . 'Person/claim-by-selfie',
			['selfie' => $selfie],
			['CONTENT_TYPE' => 'multipart/form-data', 'Accept' => 'application/json']
		);

		$this->assertCreated($response);
		self::assertEquals($this->person1->id, $response->json('id'));

		// Verify person is now linked to user
		$this->person1->refresh();
		self::assertEquals($this->userMayUpload1->id, $this->person1->user_id);
	}

	public function testSelfieClaimNoFaceDetected(): void
	{
		Http::fake([
			'fake-vision-service:8000/match' => Http::response([
				'matches' => [],
			], 200),
		]);

		$selfie = UploadedFile::fake()->image('selfie.jpg', 200, 200);

		$response = $this->actingAs($this->userMayUpload1)->post(
			self::API_PREFIX . 'Person/claim-by-selfie',
			['selfie' => $selfie],
			['CONTENT_TYPE' => 'multipart/form-data', 'Accept' => 'application/json']
		);

		$this->assertNotFound($response);
	}

	public function testSelfieClaimAlreadyClaimed(): void
	{
		$face = Face::factory()->for_photo($this->photo1)->for_person($this->person1)->create();

		$this->person1->user_id = $this->userMayUpload2->id;
		$this->person1->save();

		Http::fake([
			'fake-vision-service:8000/match' => Http::response([
				'matches' => [
					[
						'lychee_face_id' => $face->id,
						'person_id' => $this->person1->id,
						'confidence' => 0.95,
					],
				],
			], 200),
		]);

		$selfie = UploadedFile::fake()->image('selfie.jpg', 200, 200);

		$response = $this->actingAs($this->userMayUpload1)->post(
			self::API_PREFIX . 'Person/claim-by-selfie',
			['selfie' => $selfie],
			['CONTENT_TYPE' => 'multipart/form-data', 'Accept' => 'application/json']
		);

		$this->assertConflict($response);
	}

	public function testSelfieClaimAsGuestUnauthorized(): void
	{
		$selfie = UploadedFile::fake()->image('selfie.jpg', 200, 200);

		$response = $this->post(
			self::API_PREFIX . 'Person/claim-by-selfie',
			['selfie' => $selfie],
			['CONTENT_TYPE' => 'multipart/form-data', 'Accept' => 'application/json']
		);

		$this->assertUnauthorized($response);
	}
}
