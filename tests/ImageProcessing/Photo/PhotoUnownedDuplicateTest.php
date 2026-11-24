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

use App\Constants\PhotoAlbum as PA;
use App\Exceptions\PhotoRejectedException;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
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
	 * GHSA-9m66-3ccm-jfxg: a guest upload is owned by the album owner,
	 * so the ownership check alone would link the owner's private photo into the public album.
	 * A guest owns no photo: every duplicate is rejected, whatever `skip_duplicates_not_owned` says,
	 * and the existing photo is left untouched.
	 */
	#[DataProvider('skipDuplicatesNotOwnedProvider')]
	public function testGuestDuplicateIsRejected(bool $skip_duplicates_not_owned): void
	{
		Configs::set('skip_duplicates_not_owned', $skip_duplicates_not_owned);

		$public_album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		AccessPermission::factory()->public()->visible()->grants_upload()->for_album($public_album)->create();

		$this->catchFailureSilence = [];
		$response = $this->actingAs($this->userMayUpload1)->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE, album_id: $this->album1->id);
		$this->assertCreated($response);
		$photo_ids = $this->photoIdsOf($this->userMayUpload1->id);
		self::assertCount(1, $photo_ids);
		Photo::query()->whereKey($photo_ids[0])->update(['is_highlighted' => true]);

		auth()->logout();
		$this->catchFailureSilence = [PhotoRejectedException::class];
		$response = $this->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE, album_id: $public_album->id);
		$this->assertConflict($response);

		// The photo stays only in the private album, keeps its highlight, and no copy is created.
		self::assertEquals($photo_ids, $this->photoIdsOf($this->userMayUpload1->id));
		self::assertEquals(
			[$this->album1->id],
			DB::table(PA::PHOTO_ALBUM)->where('photo_id', '=', $photo_ids[0])->pluck('album_id')->all()
		);
		self::assertTrue(Photo::query()->findOrFail($photo_ids[0])->is_highlighted);

		$this->catchFailureSilence = ["App\Exceptions\MediaFileOperationException"];
	}

	/**
	 * @return array<string,array{bool}>
	 */
	public static function skipDuplicatesNotOwnedProvider(): array
	{
		return [
			'skip_duplicates_not_owned enabled' => [true],
			'skip_duplicates_not_owned disabled' => [false],
		];
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
