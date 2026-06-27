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

use App\Enum\NsfwDetectionLabel;
use App\Models\Configs;
use App\Models\NsfwDetection;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class PhotoNsfwDetectionsTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_nsfw_enabled', '1');
	}

	public function tearDown(): void
	{
		DB::table('nsfw_detections')->delete();
		parent::tearDown();
	}

	public function testOwnerGetsNsfwDetectionsPayload(): void
	{
		NsfwDetection::create([
			'photo_id' => $this->photo1->id,
			'label' => NsfwDetectionLabel::FEMALE_BREAST_EXPOSED,
			'confidence' => 0.95,
			'bbox_x' => 100,
			'bbox_y' => 200,
			'bbox_width' => 50,
			'bbox_height' => 60,
			'is_block' => true,
			'is_review' => false,
			'is_sensitive' => false,
		]);
		NsfwDetection::create([
			'photo_id' => $this->photo1->id,
			'label' => NsfwDetectionLabel::BELLY_EXPOSED,
			'confidence' => 0.72,
			'bbox_x' => 150,
			'bbox_y' => 300,
			'bbox_width' => 80,
			'bbox_height' => 90,
			'is_block' => false,
			'is_review' => false,
			'is_sensitive' => true,
		]);

		/** @var Authenticatable $owner */
		$owner = $this->userMayUpload1;

		$response = $this->actingAs($owner)->getJson('Photo/' . $this->photo1->id . '/nsfw-detections');

		$this->assertOk($response);
		self::assertCount(2, $response->json('detections'));
		self::assertIsInt($response->json('image_width'));
		self::assertIsInt($response->json('image_height'));

		$first = $response->json('detections.0');
		self::assertSame('FEMALE_BREAST_EXPOSED', $first['label']);
		self::assertSame(0.95, $first['confidence']);
		self::assertSame(100, $first['bbox_x']);
		self::assertSame(200, $first['bbox_y']);
		self::assertTrue($first['is_block']);
	}

	public function testGuestCannotGetNsfwDetections(): void
	{
		NsfwDetection::create([
			'photo_id' => $this->photo1->id,
			'label' => NsfwDetectionLabel::FACE_FEMALE,
			'confidence' => 0.80,
			'bbox_x' => 10,
			'bbox_y' => 20,
			'bbox_width' => 30,
			'bbox_height' => 40,
			'is_block' => false,
			'is_review' => true,
			'is_sensitive' => false,
		]);

		$response = $this->getJson('Photo/' . $this->photo1->id . '/nsfw-detections');

		$this->assertUnauthorized($response);
	}

	public function testEmptyDetectionsReturnsEmptyArray(): void
	{
		/** @var Authenticatable $owner */
		$owner = $this->userMayUpload1;

		$response = $this->actingAs($owner)->getJson('Photo/' . $this->photo1->id . '/nsfw-detections');

		$this->assertOk($response);
		self::assertCount(0, $response->json('detections'));
	}
}
