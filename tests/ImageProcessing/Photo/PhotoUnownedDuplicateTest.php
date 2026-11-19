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

namespace Tests\ImageProcessing\Photo;

use App\Exceptions\PhotoRejectedException;
use App\Models\Configs;
use App\Models\Photo;
use Tests\Constants\TestConstants;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class PhotoUnownedDuplicateTest extends BaseApiWithDataTest
{
	public function tearDown(): void
	{
		Configs::set('skip_duplicates_not_owned', true);
		parent::tearDown();
	}

	public function testDuplicateOfAnotherUserIsRejected(): void
	{
		Configs::set('skip_duplicates_not_owned', true);

		$this->catchFailureSilence = [];
		$response = $this->actingAs($this->userMayUpload1)->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE);
		$this->assertCreated($response);
		$photo_ids = $this->photoIdsOf($this->userMayUpload1->id);
		self::assertCount(1, $photo_ids);

		$this->catchFailureSilence = [PhotoRejectedException::class];
		$response = $this->actingAs($this->userMayUpload2)->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE);
		$this->assertConflict($response);

		// The original stays with its owner and no copy is created for the second user.
		self::assertEquals($photo_ids, $this->photoIdsOf($this->userMayUpload1->id));
		self::assertCount(0, $this->photoIdsOf($this->userMayUpload2->id));

		$this->catchFailureSilence = ["App\Exceptions\MediaFileOperationException"];
	}

	public function testDuplicateOfAnotherUserIsAcceptedWhenDisabled(): void
	{
		Configs::set('skip_duplicates_not_owned', false);

		$this->catchFailureSilence = [];
		$response = $this->actingAs($this->userMayUpload1)->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE);
		$this->assertCreated($response);

		$response = $this->actingAs($this->userMayUpload2)->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE);
		$this->assertCreated($response);

		$this->catchFailureSilence = ["App\Exceptions\MediaFileOperationException"];
	}

	public function testOwnDuplicateIsNotRejected(): void
	{
		Configs::set('skip_duplicates_not_owned', true);

		$this->catchFailureSilence = [];
		$response = $this->actingAs($this->userMayUpload1)->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE);
		$this->assertCreated($response);

		$response = $this->actingAs($this->userMayUpload1)->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE);
		$this->assertCreated($response);

		$this->catchFailureSilence = ["App\Exceptions\MediaFileOperationException"];
	}

	/**
	 * @return string[]
	 */
	private function photoIdsOf(int $owner_id): array
	{
		return Photo::query()
			->where('owner_id', '=', $owner_id)
			->where('checksum', '=', sha1_file(base_path(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)))
			->pluck('id')
			->all();
	}
}
