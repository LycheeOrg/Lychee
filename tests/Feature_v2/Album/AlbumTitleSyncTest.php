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

namespace Tests\Feature_v2\Album;

use App\Enum\RenamerModeType;
use App\Models\Album;
use App\Models\BaseAlbumImpl;
use App\Models\RenamerRule;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 060 (FR-060-03, S-060-09, S-060-12): one assertion per album write
 * site, exercising the real write path (never shortcutting via direct model
 * manipulation) and asserting `title_base`/`title_index` were synced.
 */
class AlbumTitleSyncTest extends BaseApiWithDataTest
{
	/**
	 * Write site 1/8: `Actions\Album\Create`.
	 */
	public function testCreateAlbumSyncsTitleBase(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Album', [
			'parent_id' => null,
			'title' => 'created_album_3',
		]);
		self::assertEquals(200, $response->getStatusCode());
		$album_id = $response->getOriginalContent();

		$album = BaseAlbumImpl::query()->findOrFail($album_id);
		$this->assertSame('created_album_', $album->title_base);
		$this->assertSame(3, $album->title_index);
	}

	/**
	 * Write site 2/8: `Actions\Album\CreateTagAlbum`.
	 */
	public function testCreateTagAlbumSyncsTitleBase(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('TagAlbum', [
			'title' => 'tag_album_4',
			'tags' => ['tag1'],
			'is_and' => '1',
		]);
		$this->assertOk($response);
		$album_id = $response->getOriginalContent();

		$album = BaseAlbumImpl::query()->findOrFail($album_id);
		$this->assertSame('tag_album_', $album->title_base);
		$this->assertSame(4, $album->title_index);
	}

	/**
	 * Write site 3/8: `Actions\Album\CreatePersonAlbum`.
	 */
	public function testCreatePersonAlbumSyncsTitleBase(): void
	{
		\Configs::set('ai_vision_enabled', '1');
		\Configs::set('ai_vision_face_enabled', '1');
		$person = \App\Models\Person::factory()->create(['name' => 'Alice', 'is_searchable' => true]);

		$response = $this->actingAs($this->userMayUpload1)->postJson('PersonAlbum', [
			'title' => 'person_album_6',
			'persons' => [$person->id],
			'is_and' => false,
		]);
		$this->assertOk($response);
		$album_id = $response->getOriginalContent();

		$album = BaseAlbumImpl::query()->findOrFail($album_id);
		$this->assertSame('person_album_', $album->title_base);
		$this->assertSame(6, $album->title_index);
	}

	/**
	 * Write site 4/8: `Actions\Album\SetHeader` (shared save choke-point for
	 * the regular-album title edit path, `AlbumController::updateAlbum()`).
	 */
	public function testUpdateAlbumSyncsTitleBase(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', [
			'album_id' => $this->album1->id,
			'title' => 'updated_album_8',
			'license' => 'none',
			'description' => '',
			'tags' => [],
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'album_sorting_column' => 'title',
			'album_sorting_order' => 'DESC',
			'album_aspect_ratio' => '1/1',
			'photo_layout' => null,
			'copyright' => '',
			'is_compact' => false,
			'is_pinned' => false,
			'header_id' => null,
			'album_timeline' => null,
			'photo_timeline' => null,
		]);
		$this->assertOk($response);

		$this->album1->refresh();
		$this->assertSame('updated_album_', $this->album1->title_base);
		$this->assertSame(8, $this->album1->title_index);
	}

	/**
	 * Write site 5/8: `AlbumController::updateTagAlbum()`.
	 */
	public function testUpdateTagAlbumSyncsTitleBase(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('TagAlbum', [
			'album_id' => $this->tagAlbum1->id,
			'title' => 'updated_tag_album_2',
			'tags' => ['tag1'],
			'description' => '',
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'copyright' => '',
			'is_pinned' => false,
			'is_and' => true,
			'photo_layout' => null,
			'photo_timeline' => null,
		]);
		$this->assertOk($response);

		$this->tagAlbum1->refresh();
		$this->assertSame('updated_tag_album_', $this->tagAlbum1->title_base);
		$this->assertSame(2, $this->tagAlbum1->title_index);
	}

	/**
	 * Write site 6/8: `AlbumController::updatePersonAlbum()`.
	 */
	public function testUpdatePersonAlbumSyncsTitleBase(): void
	{
		\Configs::set('ai_vision_enabled', '1');
		\Configs::set('ai_vision_face_enabled', '1');
		$person = \App\Models\Person::factory()->create(['name' => 'Bob', 'is_searchable' => true]);

		$response = $this->actingAs($this->userMayUpload1)->postJson('PersonAlbum', [
			'title' => 'person_album_seed',
			'persons' => [$person->id],
			'is_and' => false,
		]);
		$this->assertOk($response);
		$album_id = $response->getOriginalContent();

		$response = $this->actingAs($this->userMayUpload1)->patchJson('PersonAlbum', [
			'album_id' => $album_id,
			'title' => 'updated_person_album_9',
			'description' => '',
			'persons' => [$person->id],
			'is_and' => true,
			'photo_sorting_column' => null,
			'photo_sorting_order' => null,
			'copyright' => null,
			'photo_layout' => null,
			'photo_timeline' => null,
			'is_pinned' => false,
		]);
		$this->assertOk($response);

		$album = BaseAlbumImpl::query()->findOrFail($album_id);
		$this->assertSame('updated_person_album_', $album->title_base);
		$this->assertSame(9, $album->title_index);
	}

	/**
	 * Write site 7/8: `AlbumController::rename()`.
	 */
	public function testRenameAlbumSyncsTitleBase(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album::rename', [
			'album_id' => $this->album1->id,
			'title' => 'renamed_album_11',
		]);
		$this->assertNoContent($response);

		$this->album1->refresh();
		$this->assertSame('renamed_album_', $this->album1->title_base);
		$this->assertSame(11, $this->album1->title_index);
	}

	/**
	 * Write site 8/8: `Actions\Renamer\RenameAlbums` (bulk, `batch()->update()`
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
			->rule('bulk_album_rule')
			->description('Bulk album rule')
			->needle('OLD_')
			->replacement('NEW_')
			->mode(RenamerModeType::FIRST)
			->state([
				'is_enabled' => true,
				'is_photo_rule' => false,
				'is_album_rule' => true,
			])
			->create();

		$album = Album::factory()
			->owned_by($this->userMayUpload1)
			->as_root()
			->with_title('OLD_10')
			->create();

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Renamer', [
			'album_ids' => [$album->id],
		]);
		$this->assertNoContent($response);

		$album->refresh();
		$this->assertSame('NEW_10', $album->title);
		$this->assertSame('new_', $album->title_base);
		$this->assertSame(10, $album->title_index);

		\Configs::set('renamer_enabled', false);
		$this->resetSe();
	}
}
