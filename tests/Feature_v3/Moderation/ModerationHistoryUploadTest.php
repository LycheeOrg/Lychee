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

namespace Tests\Feature_v3\Moderation;

use App\Enum\ModerationEvent;
use App\Enum\ModerationSource;
use App\Enum\UserUploadTrustLevel;
use App\Jobs\DispatchNsfwScanJob;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\PhotoModerationEvent;
use Illuminate\Support\Facades\Queue;
use Tests\Constants\TestConstants;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 089 (FR-089-02, S-089-01 … S-089-03, S-089-10): holds recorded at upload.
 */
class ModerationHistoryUploadTest extends BaseApiWithDataTest
{
	private const CONFIGS = [
		'ai_vision_enabled',
		'ai_vision_nsfw_enabled',
		'ai_vision_nsfw_monitor_hide_on_scan',
		'moderation_history_log_system',
	];

	/** @var array<string,string> */
	private array $saved_configs = [];

	public function setUp(): void
	{
		parent::setUp();
		foreach (self::CONFIGS as $key) {
			$this->saved_configs[$key] = Configs::query()->where('key', '=', $key)->value('value');
		}
		Queue::fake([DispatchNsfwScanJob::class]);
	}

	public function tearDown(): void
	{
		foreach ($this->saved_configs as $key => $value) {
			Configs::set($key, $value);
		}
		$this->userMayUpload1->upload_trust_level = UserUploadTrustLevel::TRUSTED;
		$this->userMayUpload1->save();
		parent::tearDown();
	}

	public function testCheckUploadIsRecordedAsHeld(): void
	{
		$photo = $this->uploadAs(UserUploadTrustLevel::CHECK);

		self::assertFalse($photo->is_validated);
		$event = PhotoModerationEvent::query()->where('photo_id', '=', $photo->id)->sole();
		self::assertSame(ModerationEvent::HELD, $event->event);
		self::assertSame(ModerationSource::UPLOAD, $event->source);
		self::assertNull($event->user_id);
	}

	public function testMonitorUploadHiddenOnScanIsRecordedAsHeld(): void
	{
		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_nsfw_enabled', '1');
		Configs::set('ai_vision_nsfw_monitor_hide_on_scan', '1');

		$photo = $this->uploadAs(UserUploadTrustLevel::MONITOR);

		self::assertFalse($photo->is_validated);
		$event = PhotoModerationEvent::query()->where('photo_id', '=', $photo->id)->sole();
		self::assertSame(ModerationEvent::HELD, $event->event);
		self::assertSame(ModerationSource::UPLOAD, $event->source);
		self::assertNull($event->user_id);
	}

	public function testTrustedUploadIsNotRecorded(): void
	{
		$photo = $this->uploadAs(UserUploadTrustLevel::TRUSTED);

		self::assertTrue($photo->is_validated);
		self::assertSame(0, PhotoModerationEvent::query()->where('photo_id', '=', $photo->id)->count());
	}

	public function testCheckUploadIsNotRecordedWhenSystemLoggingIsOff(): void
	{
		Configs::set('moderation_history_log_system', '0');

		$photo = $this->uploadAs(UserUploadTrustLevel::CHECK);

		self::assertFalse($photo->is_validated);
		self::assertSame(0, PhotoModerationEvent::query()->where('photo_id', '=', $photo->id)->count());
	}

	private function uploadAs(UserUploadTrustLevel $trust_level): Photo
	{
		$this->userMayUpload1->upload_trust_level = $trust_level;
		$this->userMayUpload1->save();

		$existing_ids = Photo::query()->pluck('id')->all();

		$response = $this->actingAs($this->userMayUpload1)->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE, album_id: $this->album1->id);
		$this->assertCreated($response);

		return Photo::query()->whereNotIn('id', $existing_ids)->sole();
	}
}
