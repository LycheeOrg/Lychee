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

use App\Enum\NsfwSensitiveNoAlbumAction;
use App\Enum\NsfwStatus;
use App\Jobs\ApplyNsfwAlbumSensitivityJob;
use App\Models\BaseAlbumImpl;
use App\Models\Configs;
use App\Models\Photo;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class ApplyNsfwAlbumSensitivityJobTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();

		Configs::set('ai_vision_nsfw_sensitive_no_album_action', NsfwSensitiveNoAlbumAction::SKIP->value);
	}

	public function testMarksAlbumAsNsfw(): void
	{
		self::assertFalse($this->album1->is_nsfw);

		$job = new ApplyNsfwAlbumSensitivityJob($this->photo1->id);
		$job->handle(resolve(\App\Repositories\ConfigManager::class));

		$this->album1->refresh();
		self::assertTrue($this->album1->is_nsfw);
	}

	public function testSkipsWhenPhotoNotFound(): void
	{
		$job = new ApplyNsfwAlbumSensitivityJob('nonexistent-photo-id');
		$job->handle(resolve(\App\Repositories\ConfigManager::class));

		// Should not throw
		self::assertTrue(true);
	}

	public function testNoAlbumFallbackSkipLeavesPhotoAlone(): void
	{
		Configs::set('ai_vision_nsfw_sensitive_no_album_action', NsfwSensitiveNoAlbumAction::SKIP->value);
		Photo::where('id', $this->photoUnsorted->id)->update(['nsfw_status' => NsfwStatus::VISIBLE->value]);

		$job = new ApplyNsfwAlbumSensitivityJob($this->photoUnsorted->id);
		$job->handle(resolve(\App\Repositories\ConfigManager::class));

		$this->photoUnsorted->refresh();
		self::assertEquals(NsfwStatus::VISIBLE, $this->photoUnsorted->nsfw_status);
	}

	public function testNoAlbumFallbackModerateSetsReview(): void
	{
		Configs::set('ai_vision_nsfw_sensitive_no_album_action', NsfwSensitiveNoAlbumAction::MODERATE->value);
		Photo::where('id', $this->photoUnsorted->id)->update(['nsfw_status' => NsfwStatus::VISIBLE->value]);

		$job = new ApplyNsfwAlbumSensitivityJob($this->photoUnsorted->id);
		$job->handle(resolve(\App\Repositories\ConfigManager::class));

		$this->photoUnsorted->refresh();
		self::assertEquals(NsfwStatus::REVIEW, $this->photoUnsorted->nsfw_status);
		self::assertFalse($this->photoUnsorted->is_validated);
	}

	public function testSkipsAlbumAlreadyMarkedNsfw(): void
	{
		BaseAlbumImpl::where('id', $this->album1->id)->update(['is_nsfw' => true]);

		$job = new ApplyNsfwAlbumSensitivityJob($this->photo1->id);
		$job->handle(resolve(\App\Repositories\ConfigManager::class));

		$this->album1->refresh();
		self::assertTrue($this->album1->is_nsfw);
	}
}
