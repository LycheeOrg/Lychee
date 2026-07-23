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

use App\Enum\NsfwBlockFindingAction;
use App\Enum\NsfwStatus;
use App\Models\Configs;
use App\Models\NsfwDetection;
use App\Models\Photo;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class NsfwDetectionResultsTest extends BaseApiWithDataTest
{
	private string $api_key;

	public function setUp(): void
	{
		parent::setUp();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_nsfw_enabled', '1');
		Configs::set('ai_vision_nsfw_check_block_action', NsfwBlockFindingAction::BLOCK->value);
		Configs::set('ai_vision_nsfw_monitor_block_action', NsfwBlockFindingAction::MODERATE->value);

		$this->api_key = 'test-nsfw-api-key-12345';
		config(['services.nsfw_detection.api_key' => $this->api_key]);
	}

	public function tearDown(): void
	{
		DB::table('nsfw_detections')->delete();
		parent::tearDown();
	}

	// ── AUTH ────────────────────────────────────────────────────

	public function testResultsInvalidApiKeyUnauthorized(): void
	{
		$response = $this->postJson('NsfwDetection/results', [
			'photo_id' => $this->photo1->id,
			'status' => 'success',
		], ['X-API-Key' => 'wrong-key']);

		$this->assertUnauthorized($response);
	}

	public function testResultsMissingApiKeyUnauthorized(): void
	{
		$response = $this->postJson('NsfwDetection/results', [
			'photo_id' => $this->photo1->id,
			'status' => 'success',
		]);

		$this->assertUnauthorized($response);
	}

	// ── ERROR STATUS ────────────────────────────────────────────

	public function testResultsErrorStatusMarksPhotoFailed(): void
	{
		Photo::where('id', $this->photo1->id)->update(['nsfw_status' => NsfwStatus::PENDING->value]);

		$response = $this->postJson('NsfwDetection/results', [
			'photo_id' => $this->photo1->id,
			'status' => 'error',
			'error_code' => 'TIMEOUT',
			'message' => 'Classification timed out',
		], ['X-API-Key' => $this->api_key]);

		$this->assertOk($response);

		$this->photo1->refresh();
		self::assertEquals(NsfwStatus::FAILED, $this->photo1->nsfw_status);
	}

	// ── NONEXISTENT PHOTO ───────────────────────────────────────

	public function testResultsNonexistentPhotoReturnsOk(): void
	{
		$response = $this->postJson('NsfwDetection/results', [
			'photo_id' => 'nonexistent-photo-id',
			'status' => 'success',
		], ['X-API-Key' => $this->api_key]);

		$this->assertOk($response);
	}

	// ── SUCCESS: CLEAN RESULT ───────────────────────────────────

	public function testResultsCleanSuccessMarksVisible(): void
	{
		Photo::where('id', $this->photo1->id)->update(['nsfw_status' => NsfwStatus::PENDING->value]);

		$response = $this->postJson('NsfwDetection/results', [
			'photo_id' => $this->photo1->id,
			'status' => 'success',
			'should_block' => false,
			'should_review' => false,
			'is_sensitive' => false,
		], ['X-API-Key' => $this->api_key]);

		$this->assertOk($response);

		$this->photo1->refresh();
		self::assertEquals(NsfwStatus::VISIBLE, $this->photo1->nsfw_status);
	}

	// ── SUCCESS: BLOCK FINDING ──────────────────────────────────

	public function testResultsBlockFindingModeratesPhoto(): void
	{
		Configs::set('ai_vision_nsfw_monitor_block_action', NsfwBlockFindingAction::MODERATE->value);
		Photo::where('id', $this->photo1->id)->update([
			'nsfw_status' => NsfwStatus::PENDING->value,
			'upload_trust_level' => 'monitor',
		]);

		$response = $this->postJson('NsfwDetection/results', [
			'photo_id' => $this->photo1->id,
			'status' => 'success',
			'should_block' => true,
			'should_review' => false,
			'is_sensitive' => false,
			'block_detected' => [
				[
					'label' => 'FEMALE_GENITALIA_EXPOSED',
					'confidence' => 0.95,
					'bbox' => ['x' => 100, 'y' => 200, 'width' => 50, 'height' => 60],
				],
			],
		], ['X-API-Key' => $this->api_key]);

		$this->assertOk($response);

		$this->photo1->refresh();
		self::assertEquals(NsfwStatus::REVIEW, $this->photo1->nsfw_status);
		self::assertFalse($this->photo1->is_validated);
	}

	// ── SUCCESS: DETECTIONS LOGGED ──────────────────────────────

	public function testResultsLogDetectionsToDatabase(): void
	{
		Photo::where('id', $this->photo1->id)->update([
			'nsfw_status' => NsfwStatus::PENDING->value,
			'upload_trust_level' => 'trusted',
		]);
		Configs::set('ai_vision_nsfw_trust_block_action', NsfwBlockFindingAction::APPROVE->value);

		$response = $this->postJson('NsfwDetection/results', [
			'photo_id' => $this->photo1->id,
			'status' => 'success',
			'should_block' => true,
			'should_review' => false,
			'is_sensitive' => false,
			'block_detected' => [
				[
					'label' => 'FEMALE_BREAST_EXPOSED',
					'confidence' => 0.88,
					'bbox' => ['x' => 10, 'y' => 20, 'width' => 30, 'height' => 40],
					'area_pixels' => 1200,
					'area_ratio' => 0.05,
				],
			],
			'review_detected' => [
				[
					'label' => 'BUTTOCKS_EXPOSED',
					'confidence' => 0.72,
					'bbox' => ['x' => 50, 'y' => 60, 'width' => 25, 'height' => 35],
				],
			],
		], ['X-API-Key' => $this->api_key]);

		$this->assertOk($response);

		$detections = NsfwDetection::where('photo_id', $this->photo1->id)->get();
		self::assertCount(2, $detections);

		$block = $detections->firstWhere('is_block', true);
		self::assertNotNull($block);
		self::assertEquals('FEMALE_BREAST_EXPOSED', $block->label->value);
		self::assertEqualsWithDelta(0.88, $block->confidence, 0.001);

		$review = $detections->firstWhere('is_review', true);
		self::assertNotNull($review);
		self::assertEquals('BUTTOCKS_EXPOSED', $review->label->value);
	}

	// ── VALIDATION ──────────────────────────────────────────────

	public function testResultsValidationRequiresPhotoId(): void
	{
		$response = $this->postJson('NsfwDetection/results', [
			'status' => 'success',
		], ['X-API-Key' => $this->api_key]);

		$this->assertUnprocessable($response);
	}

	public function testResultsValidationRequiresStatus(): void
	{
		$response = $this->postJson('NsfwDetection/results', [
			'photo_id' => $this->photo1->id,
		], ['X-API-Key' => $this->api_key]);

		$this->assertUnprocessable($response);
	}

	public function testResultsValidationRejectsInvalidStatus(): void
	{
		$response = $this->postJson('NsfwDetection/results', [
			'photo_id' => $this->photo1->id,
			'status' => 'invalid',
		], ['X-API-Key' => $this->api_key]);

		$this->assertUnprocessable($response);
	}
}
