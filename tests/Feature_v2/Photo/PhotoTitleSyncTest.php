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

namespace Tests\Feature_v2\Photo;

use App\Enum\RenamerModeType;
use App\Models\Photo;
use App\Models\RenamerRule;
use Tests\Constants\TestConstants;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 060 (FR-060-03, S-060-12): one assertion per photo write site,
 * exercising the real write path (never shortcutting via direct model
 * manipulation) and asserting `title_base`/`title_index` were synced.
 */
class PhotoTitleSyncTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		config(['queue.default' => 'sync']);
	}

	/**
	 * Write site 1/12: `Pipes\Shared\Save` (upload pipeline choke-point).
	 */
	public function testUploadSyncsTitleBase(): void
	{
		$data = [
			'album_id' => $this->album5->id,
			'file' => static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			'file_last_modified_time' => 1678824303000,
			'file_name' => TestConstants::SAMPLE_FILE_NIGHT_IMAGE,
			'uuid_name' => '',
			'extension' => '',
			'chunk_number' => 1,
			'total_chunks' => 1,
			'title' => 'upload_title_5',
		];

		$response = $this->actingAs($this->admin)->upload('Photo', data: $data);
		$this->assertCreated($response);

		$expected_id = $response->json('expected_id');
		$photo = Photo::query()->findOrFail($expected_id);
		$this->assertSame('upload_title_', $photo->title_base);
		$this->assertSame(5, $photo->title_index);
	}

	/**
	 * Write site 2/12: `PhotoController::update()`.
	 */
	public function testUpdateSyncsTitleBase(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo', [
			'photo_id' => $this->photo1->id,
			'title' => 'updated_title_12',
			'description' => '',
			'tags' => [],
			'license' => 'none',
			'taken_at' => null,
			'upload_date' => '2021-01-01',
			'from_id' => $this->album1->id,
		]);
		$this->assertOk($response);

		$this->photo1->refresh();
		$this->assertSame('updated_title_', $this->photo1->title_base);
		$this->assertSame(12, $this->photo1->title_index);
	}

	/**
	 * Write site 3/12: `PhotoController::rename()`.
	 */
	public function testRenameSyncsTitleBase(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo::rename', [
			'photo_id' => $this->photo1->id,
			'title' => 'renamed_title_7',
		]);
		$this->assertNoContent($response);

		$this->photo1->refresh();
		$this->assertSame('renamed_title_', $this->photo1->title_base);
		$this->assertSame(7, $this->photo1->title_index);
	}

	/**
	 * Write site 4/12: `Actions\Renamer\RenamePhotos` (bulk, `batch()->update()`
	 * bypasses `save()`/model events entirely - the split must be computed
	 * inline in the closure).
	 */
	public function testBulkRenamerSyncsTitleBase(): void
	{
		$this->requireSe();
		\Configs::set('renamer_enabled', true);

		RenamerRule::factory()
			->owner_id($this->userMayUpload1->id)
			->order(1)
			->rule('bulk_rule')
			->description('Bulk rule')
			->needle('OLD_')
			->replacement('NEW_')
			->mode(RenamerModeType::FIRST)
			->state([
				'is_enabled' => true,
				'is_photo_rule' => true,
				'is_album_rule' => false,
			])
			->create();

		$photo = Photo::factory()
			->owned_by($this->userMayUpload1)
			->with_title('OLD_9')
			->in($this->album1)
			->create();

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Renamer', [
			'photo_ids' => [$photo->id],
		]);
		$this->assertNoContent($response);

		$photo->refresh();
		$this->assertSame('NEW_9', $photo->title);
		$this->assertSame('new_', $photo->title_base);
		$this->assertSame(9, $photo->title_index);

		\Configs::set('renamer_enabled', false);
		$this->resetSe();
	}
}
