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

use App\Enum\ModerationEvent;
use App\Enum\ModerationSource;
use App\Enum\NsfwBlockFindingAction;
use App\Enum\NsfwSensitiveAlbumAction;
use App\Enum\NsfwSensitiveNoAlbumAction;
use App\Enum\NsfwStatus;
use App\Enum\UserUploadTrustLevel;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\PhotoModerationEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 089 (FR-089-02, FR-089-03, S-089-04 … S-089-08, S-089-11, S-089-12):
 * moderation events written by the NSFW scan callback.
 */
class ModerationHistoryNsfwTest extends BaseApiWithDataTest
{
	private const CONFIGS = [
		'ai_vision_enabled',
		'ai_vision_nsfw_enabled',
		'ai_vision_nsfw_check_block_action',
		'ai_vision_nsfw_monitor_block_action',
		'ai_vision_nsfw_sensitive_album_action',
		'ai_vision_nsfw_sensitive_no_album_action',
		'moderation_history_log_system',
		'moderation_history_log_approvals',
		'moderation_history_log_nsfw_holds',
	];

	private const API_KEY = 'test-nsfw-api-key-12345';

	private const DETECTION = [
		'label' => 'BUTTOCKS_EXPOSED',
		'confidence' => 0.82,
		'bbox' => ['x' => 10, 'y' => 20, 'width' => 30, 'height' => 40],
	];

	/** @var array<string,string|null> */
	private array $saved_configs = [];

	public function setUp(): void
	{
		parent::setUp();
		foreach (self::CONFIGS as $key) {
			$this->saved_configs[$key] = Configs::query()->where('key', '=', $key)->value('value');
		}

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_nsfw_enabled', '1');
		Configs::set('ai_vision_nsfw_check_block_action', NsfwBlockFindingAction::BLOCK->value);
		Configs::set('ai_vision_nsfw_monitor_block_action', NsfwBlockFindingAction::MODERATE->value);
		config(['services.nsfw_detection.api_key' => self::API_KEY]);
	}

	public function tearDown(): void
	{
		DB::table('nsfw_detections')->delete();
		foreach ($this->saved_configs as $key => $value) {
			Configs::set($key, $value ?? '');
		}
		parent::tearDown();
	}

	public function testReviewFindingOnMonitorPhotoIsRecordedAsHeld(): void
	{
		$this->preparePhoto($this->photo1, UserUploadTrustLevel::MONITOR, is_validated: true);

		$this->postResults($this->photo1, ['should_review' => true, 'review_detected' => [self::DETECTION]]);

		$this->assertPhotoState($this->photo1, NsfwStatus::REVIEW, false);
		$this->assertSoleEvent($this->photo1, ModerationEvent::HELD, ModerationSource::NSFW_SCAN);
	}

	public function testReviewFindingOnHiddenCheckPhotoIsRecordedAsHeld(): void
	{
		$this->preparePhoto($this->photo1, UserUploadTrustLevel::CHECK, is_validated: false);

		$this->postResults($this->photo1, ['should_review' => true, 'review_detected' => [self::DETECTION]]);

		$this->assertPhotoState($this->photo1, NsfwStatus::REVIEW, false);
		$this->assertSoleEvent($this->photo1, ModerationEvent::HELD, ModerationSource::NSFW_SCAN);
	}

	public function testBlockFindingModeratedIsRecordedAsHeld(): void
	{
		$this->preparePhoto($this->photo1, UserUploadTrustLevel::MONITOR, is_validated: true);

		$this->postResults($this->photo1, ['should_block' => true, 'block_detected' => [self::DETECTION]]);

		$this->assertPhotoState($this->photo1, NsfwStatus::REVIEW, false);
		$this->assertSoleEvent($this->photo1, ModerationEvent::HELD, ModerationSource::NSFW_SCAN);
	}

	public function testSensitiveFindingOnCheckPhotoIsRecordedAsHeld(): void
	{
		$this->preparePhoto($this->photo1, UserUploadTrustLevel::CHECK, is_validated: false);

		$this->postResults($this->photo1, ['is_sensitive' => true, 'sensitive_detected' => [self::DETECTION]]);

		$this->assertPhotoState($this->photo1, NsfwStatus::REVIEW, false);
		$this->assertSoleEvent($this->photo1, ModerationEvent::HELD, ModerationSource::NSFW_SCAN);
	}

	public function testNoAlbumFallbackIsRecordedAsHeld(): void
	{
		Configs::set('ai_vision_nsfw_sensitive_album_action', NsfwSensitiveAlbumAction::MARK_ALBUM->value);
		Configs::set('ai_vision_nsfw_sensitive_no_album_action', NsfwSensitiveNoAlbumAction::MODERATE->value);
		$this->preparePhoto($this->photoUnsorted, UserUploadTrustLevel::MONITOR, is_validated: true);

		$this->postResults($this->photoUnsorted, ['is_sensitive' => true, 'sensitive_detected' => [self::DETECTION]]);

		$this->assertPhotoState($this->photoUnsorted, NsfwStatus::REVIEW, false);
		$this->assertSoleEvent($this->photoUnsorted, ModerationEvent::HELD, ModerationSource::NSFW_SCAN);
	}

	public function testCleanResultOnHiddenMonitorPhotoIsRecordedAsApproved(): void
	{
		$this->preparePhoto($this->photo1, UserUploadTrustLevel::MONITOR, is_validated: false);

		$this->postResults($this->photo1, []);

		$this->assertPhotoState($this->photo1, NsfwStatus::VISIBLE, true);
		$this->assertSoleEvent($this->photo1, ModerationEvent::APPROVED, ModerationSource::NSFW_SCAN);
	}

	public function testCleanResultOnVisibleMonitorPhotoIsNotRecorded(): void
	{
		$this->preparePhoto($this->photo1, UserUploadTrustLevel::MONITOR, is_validated: true);

		$this->postResults($this->photo1, []);

		$this->assertPhotoState($this->photo1, NsfwStatus::VISIBLE, true);
		$this->assertNoEvent($this->photo1);
	}

	public function testCleanResultOnCheckPhotoIsNotRecorded(): void
	{
		$this->preparePhoto($this->photo1, UserUploadTrustLevel::CHECK, is_validated: false);

		$this->postResults($this->photo1, []);

		$this->assertPhotoState($this->photo1, NsfwStatus::VISIBLE, false);
		$this->assertNoEvent($this->photo1);
	}

	public function testBlockFindingDeletingThePhotoLeavesNoEvent(): void
	{
		$this->preparePhoto($this->photo1, UserUploadTrustLevel::CHECK, is_validated: false);

		$this->postResults($this->photo1, ['should_block' => true, 'block_detected' => [self::DETECTION]]);

		self::assertNull(Photo::query()->find($this->photo1->id));
		$this->assertNoEvent($this->photo1);
	}

	public function testSystemLoggingOffWritesNothing(): void
	{
		Configs::set('moderation_history_log_system', '0');
		$this->preparePhoto($this->photo1, UserUploadTrustLevel::MONITOR, is_validated: true);
		$this->preparePhoto($this->photo2, UserUploadTrustLevel::MONITOR, is_validated: false);

		$this->postResults($this->photo1, ['should_review' => true, 'review_detected' => [self::DETECTION]]);
		$this->postResults($this->photo2, []);

		$this->assertNoEvent($this->photo1);
		$this->assertNoEvent($this->photo2);
	}

	public function testApprovalLoggingOffKeepsHolds(): void
	{
		Configs::set('moderation_history_log_approvals', '0');
		$this->preparePhoto($this->photo1, UserUploadTrustLevel::MONITOR, is_validated: true);
		$this->preparePhoto($this->photo2, UserUploadTrustLevel::MONITOR, is_validated: false);

		$this->postResults($this->photo1, ['should_review' => true, 'review_detected' => [self::DETECTION]]);
		$this->postResults($this->photo2, []);

		$this->assertSoleEvent($this->photo1, ModerationEvent::HELD, ModerationSource::NSFW_SCAN);
		$this->assertNoEvent($this->photo2);
	}

	public function testNsfwHoldLoggingOffKeepsApprovals(): void
	{
		Configs::set('moderation_history_log_nsfw_holds', '0');
		$this->preparePhoto($this->photo1, UserUploadTrustLevel::MONITOR, is_validated: true);
		$this->preparePhoto($this->photo2, UserUploadTrustLevel::MONITOR, is_validated: false);

		$this->postResults($this->photo1, ['should_review' => true, 'review_detected' => [self::DETECTION]]);
		$this->postResults($this->photo2, []);

		$this->assertNoEvent($this->photo1);
		$this->assertSoleEvent($this->photo2, ModerationEvent::APPROVED, ModerationSource::NSFW_SCAN);
	}

	private function preparePhoto(Photo $photo, UserUploadTrustLevel $trust_level, bool $is_validated): void
	{
		Photo::query()->where('id', '=', $photo->id)->update([
			'nsfw_status' => NsfwStatus::PENDING->value,
			'upload_trust_level' => $trust_level->value,
			'is_validated' => $is_validated,
		]);
	}

	/**
	 * @param array<string,mixed> $findings
	 */
	private function postResults(Photo $photo, array $findings): TestResponse
	{
		$response = $this->postJson('NsfwDetection/results', array_merge([
			'photo_id' => $photo->id,
			'status' => 'success',
			'should_block' => false,
			'should_review' => false,
			'is_sensitive' => false,
		], $findings), ['X-API-Key' => self::API_KEY]);
		$this->assertOk($response);

		return $response;
	}

	private function assertPhotoState(Photo $photo, NsfwStatus $nsfw_status, bool $is_validated): void
	{
		$photo->refresh();
		self::assertSame($nsfw_status, $photo->nsfw_status);
		self::assertSame($is_validated, $photo->is_validated);
	}

	private function assertSoleEvent(Photo $photo, ModerationEvent $event, ModerationSource $source): void
	{
		$row = PhotoModerationEvent::query()->where('photo_id', '=', $photo->id)->sole();
		self::assertSame($event, $row->event);
		self::assertSame($source, $row->source);
		self::assertNull($row->user_id);
	}

	private function assertNoEvent(Photo $photo): void
	{
		self::assertSame(0, PhotoModerationEvent::query()->where('photo_id', '=', $photo->id)->count());
	}
}
