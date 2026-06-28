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

use App\Jobs\ApplyNsfwAlbumSensitivityJob;
use App\Models\BaseAlbumImpl;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class ApplyNsfwAlbumSensitivityJobTest extends BaseApiWithDataTest
{
	public function testMarksAlbumAsNsfw(): void
	{
		self::assertFalse($this->album1->is_nsfw);

		$job = new ApplyNsfwAlbumSensitivityJob([$this->album1->id]);
		$job->handle();

		$this->album1->refresh();
		self::assertTrue($this->album1->is_nsfw);
	}

	public function testSkipsNonExistentAlbumId(): void
	{
		$job = new ApplyNsfwAlbumSensitivityJob(['nonexistent-album-id']);
		$job->handle();

		self::assertTrue(true);
	}

	public function testEmptyAlbumIdsDoesNothing(): void
	{
		$job = new ApplyNsfwAlbumSensitivityJob([]);
		$job->handle();

		self::assertTrue(true);
	}

	public function testSkipsAlbumAlreadyMarkedNsfw(): void
	{
		BaseAlbumImpl::where('id', $this->album1->id)->update(['is_nsfw' => true]);

		$job = new ApplyNsfwAlbumSensitivityJob([$this->album1->id]);
		$job->handle();

		$this->album1->refresh();
		self::assertTrue($this->album1->is_nsfw);
	}
}
