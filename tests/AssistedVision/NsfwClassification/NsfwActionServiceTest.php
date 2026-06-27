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

use App\DTO\Nsfw\NsfwBboxData;
use App\DTO\Nsfw\NsfwDetectionItemData;
use App\Enum\NsfwBlockFindingAction;
use App\Enum\NsfwDetectionLabel;
use App\Enum\NsfwSensitiveAlbumAction;
use App\Enum\NsfwSensitiveNoAlbumAction;
use App\Enum\NsfwStatus;
use App\Enum\UserUploadTrustLevel;
use App\Jobs\ApplyNsfwAlbumSensitivityJob;
use App\Models\Configs;
use App\Models\NsfwDetection;
use App\Models\Photo;
use App\Services\Image\NsfwActionService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class NsfwActionServiceTest extends BaseApiWithDataTest
{
	private NsfwActionService $service;

	public function setUp(): void
	{
		parent::setUp();

		Configs::set('ai_vision_nsfw_check_block_action', NsfwBlockFindingAction::BLOCK->value);
		Configs::set('ai_vision_nsfw_monitor_block_action', NsfwBlockFindingAction::MODERATE->value);
		Configs::set('ai_vision_nsfw_trust_but_verify_block_action', NsfwBlockFindingAction::MODERATE->value);
		Configs::set('ai_vision_nsfw_trust_block_action', NsfwBlockFindingAction::APPROVE->value);
		Configs::set('ai_vision_nsfw_sensitive_album_action', NsfwSensitiveAlbumAction::MARK_ALBUM->value);
		Configs::set('ai_vision_nsfw_sensitive_no_album_action', NsfwSensitiveNoAlbumAction::SKIP->value);

		$this->service = resolve(NsfwActionService::class);
	}

	public function tearDown(): void
	{
		DB::table('nsfw_detections')->delete();
		parent::tearDown();
	}

	// ── BLOCK ACTIONS ───────────────────────────────────────────

	public function testBlockCheckUserDeletesPhoto(): void
	{
		Photo::where('id', $this->photo1->id)->update([
			'nsfw_status' => NsfwStatus::PENDING->value,
			'upload_trust_level' => UserUploadTrustLevel::CHECK->value,
		]);
		$this->photo1->refresh();

		$result = $this->service->applyActions($this->photo1, true, false, false);

		self::assertFalse($result);
		self::assertNull(Photo::find($this->photo1->id));
	}

	public function testBlockMonitorUserModeratesPhoto(): void
	{
		Photo::where('id', $this->photo1->id)->update([
			'nsfw_status' => NsfwStatus::PENDING->value,
			'upload_trust_level' => UserUploadTrustLevel::MONITOR->value,
		]);
		$this->photo1->refresh();

		$result = $this->service->applyActions($this->photo1, true, false, false);

		self::assertTrue($result);
		$this->photo1->refresh();
		self::assertEquals(NsfwStatus::REVIEW, $this->photo1->nsfw_status);
		self::assertFalse($this->photo1->is_validated);
	}

	public function testBlockTrustedUserApprovesPhoto(): void
	{
		Photo::where('id', $this->photo1->id)->update([
			'nsfw_status' => NsfwStatus::PENDING->value,
			'upload_trust_level' => UserUploadTrustLevel::TRUSTED->value,
		]);
		$this->photo1->refresh();

		$result = $this->service->applyActions($this->photo1, true, false, false);

		self::assertTrue($result);
		$this->photo1->refresh();
		self::assertEquals(NsfwStatus::VISIBLE, $this->photo1->nsfw_status);
	}

	// ── REVIEW ACTIONS ──────────────────────────────────────────

	public function testReviewCheckUserModeratesPhoto(): void
	{
		Photo::where('id', $this->photo1->id)->update([
			'nsfw_status' => NsfwStatus::PENDING->value,
			'upload_trust_level' => UserUploadTrustLevel::CHECK->value,
		]);
		$this->photo1->refresh();

		$result = $this->service->applyActions($this->photo1, false, true, false);

		self::assertTrue($result);
		$this->photo1->refresh();
		self::assertEquals(NsfwStatus::REVIEW, $this->photo1->nsfw_status);
		self::assertFalse($this->photo1->is_validated);
	}

	public function testReviewMonitorUserModeratesPhoto(): void
	{
		Photo::where('id', $this->photo1->id)->update([
			'nsfw_status' => NsfwStatus::PENDING->value,
			'upload_trust_level' => UserUploadTrustLevel::MONITOR->value,
		]);
		$this->photo1->refresh();

		$result = $this->service->applyActions($this->photo1, false, true, false);

		self::assertTrue($result);
		$this->photo1->refresh();
		self::assertEquals(NsfwStatus::REVIEW, $this->photo1->nsfw_status);
	}

	public function testReviewTrustedUserApprovesPhoto(): void
	{
		Photo::where('id', $this->photo1->id)->update([
			'nsfw_status' => NsfwStatus::PENDING->value,
			'upload_trust_level' => UserUploadTrustLevel::TRUSTED->value,
		]);
		$this->photo1->refresh();

		$result = $this->service->applyActions($this->photo1, false, true, false);

		self::assertTrue($result);
		$this->photo1->refresh();
		self::assertEquals(NsfwStatus::VISIBLE, $this->photo1->nsfw_status);
		self::assertTrue($this->photo1->is_validated);
	}

	// ── SENSITIVE ACTIONS ───────────────────────────────────────

	public function testSensitiveDispatchesAlbumSensitivityJob(): void
	{
		Bus::fake([ApplyNsfwAlbumSensitivityJob::class]);

		Photo::where('id', $this->photo1->id)->update([
			'nsfw_status' => NsfwStatus::PENDING->value,
			'upload_trust_level' => UserUploadTrustLevel::MONITOR->value,
		]);
		$this->photo1->refresh();

		$this->service->applyActions($this->photo1, false, false, true);

		Bus::assertDispatched(ApplyNsfwAlbumSensitivityJob::class);
	}

	public function testSensitiveCheckUserModeratesInsteadOfAlbumMarking(): void
	{
		Bus::fake([ApplyNsfwAlbumSensitivityJob::class]);

		Photo::where('id', $this->photo1->id)->update([
			'nsfw_status' => NsfwStatus::PENDING->value,
			'upload_trust_level' => UserUploadTrustLevel::CHECK->value,
		]);
		$this->photo1->refresh();

		$this->service->applyActions($this->photo1, false, false, true);

		Bus::assertNotDispatched(ApplyNsfwAlbumSensitivityJob::class);
		$this->photo1->refresh();
		self::assertEquals(NsfwStatus::REVIEW, $this->photo1->nsfw_status);
	}

	public function testSensitiveNothingActionDoesNotDispatch(): void
	{
		Bus::fake([ApplyNsfwAlbumSensitivityJob::class]);
		Configs::set('ai_vision_nsfw_sensitive_album_action', NsfwSensitiveAlbumAction::NOTHING->value);

		Photo::where('id', $this->photo1->id)->update([
			'nsfw_status' => NsfwStatus::PENDING->value,
			'upload_trust_level' => UserUploadTrustLevel::MONITOR->value,
		]);
		$this->photo1->refresh();

		$this->service->applyActions($this->photo1, false, false, true);

		Bus::assertNotDispatched(ApplyNsfwAlbumSensitivityJob::class);
	}

	// ── CLEAN RESULT ────────────────────────────────────────────

	public function testCleanResultSetsVisibleAndRestoresValidation(): void
	{
		Photo::where('id', $this->photo1->id)->update([
			'nsfw_status' => NsfwStatus::PENDING->value,
			'upload_trust_level' => UserUploadTrustLevel::MONITOR->value,
			'is_validated' => false,
		]);
		$this->photo1->refresh();

		$result = $this->service->applyActions($this->photo1, false, false, false);

		self::assertTrue($result);
		$this->photo1->refresh();
		self::assertEquals(NsfwStatus::VISIBLE, $this->photo1->nsfw_status);
		self::assertTrue($this->photo1->is_validated);
	}

	// ── DETECTION LOGGING ───────────────────────────────────────

	public function testLogDetectionsCreatesRecords(): void
	{
		$block = [
			new NsfwDetectionItemData(
				label: NsfwDetectionLabel::FEMALE_BREAST_EXPOSED,
				confidence: 0.95,
				bbox: new NsfwBboxData(x: 10, y: 20, width: 30, height: 40),
				area_pixels: 1200,
				area_ratio: 0.05,
			),
		];
		$review = [
			new NsfwDetectionItemData(
				label: NsfwDetectionLabel::BUTTOCKS_EXPOSED,
				confidence: 0.72,
				bbox: new NsfwBboxData(x: 50, y: 60, width: 25, height: 35),
				area_pixels: 875,
				area_ratio: 0.03,
			),
		];
		$sensitive = [];

		$this->service->logDetections($this->photo1->id, $block, $review, $sensitive);

		$detections = NsfwDetection::where('photo_id', $this->photo1->id)->get();
		self::assertCount(2, $detections);
		self::assertTrue($detections->firstWhere('label', 'FEMALE_BREAST_EXPOSED')->is_block);
		self::assertTrue($detections->firstWhere('label', 'BUTTOCKS_EXPOSED')->is_review);
	}

	public function testLogDetectionsDeduplicatesSameBbox(): void
	{
		$detection = new NsfwDetectionItemData(
			label: NsfwDetectionLabel::FEMALE_BREAST_EXPOSED,
			confidence: 0.95,
			bbox: new NsfwBboxData(x: 10, y: 20, width: 30, height: 40),
			area_pixels: 1200,
			area_ratio: 0.05,
		);

		$this->service->logDetections($this->photo1->id, [$detection], [$detection], []);

		$detections = NsfwDetection::where('photo_id', $this->photo1->id)->get();
		self::assertCount(1, $detections);
		self::assertTrue($detections->first()->is_block);
		self::assertTrue($detections->first()->is_review);
	}
}
