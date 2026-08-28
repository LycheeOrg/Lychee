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

namespace Tests\Feature_v2;

use App\Enum\RenamerModeType;
use App\Models\Album;
use App\Models\BaseAlbumImpl;
use App\Models\Person;
use App\Models\Photo;
use App\Models\RenamerRule;
use App\Services\TitleSplitter;
use Tests\Constants\TestConstants;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 060 (NFR-060-07, S-060-15): the regression guard for the
 * "explicit call, no hook" trade-off. Exercises all 12 write sites, then
 * asserts every resulting row's `title_base`/`title_index` equals a fresh
 * `TitleSplitter::split(title)` recomputation - this is what would catch a
 * future write site that forgets the explicit call.
 */
class TitleSplitIntegrityTest extends BaseApiWithDataTest
{
	/** @var string[] */
	private array $touched_photo_ids = [];

	/** @var string[] */
	private array $touched_album_ids = [];

	public function setUp(): void
	{
		parent::setUp();
		config(['queue.default' => 'sync']);
	}

	public function testAllWriteSitesProduceConsistentDerivedColumns(): void
	{
		$this->exercisePhotoWriteSites();
		$this->exerciseAlbumWriteSites();

		$this->assertNotEmpty($this->touched_photo_ids);
		$this->assertNotEmpty($this->touched_album_ids);

		foreach (Photo::query()->whereIn('id', $this->touched_photo_ids)->get() as $photo) {
			$expected = TitleSplitter::split($photo->title);
			$this->assertSame($expected->base, $photo->title_base, 'title_base mismatch for photo ' . $photo->id . ' (title: ' . $photo->title . ')');
			$this->assertSame($expected->index, $photo->title_index, 'title_index mismatch for photo ' . $photo->id . ' (title: ' . $photo->title . ')');
		}

		foreach (BaseAlbumImpl::query()->whereIn('id', $this->touched_album_ids)->get() as $album) {
			$expected = TitleSplitter::split($album->title);
			$this->assertSame($expected->base, $album->title_base, 'title_base mismatch for album ' . $album->id . ' (title: ' . $album->title . ')');
			$this->assertSame($expected->index, $album->title_index, 'title_index mismatch for album ' . $album->id . ' (title: ' . $album->title . ')');
		}
	}

	private function exercisePhotoWriteSites(): void
	{
		// 1/4: Pipes\Shared\Save (upload pipeline choke-point).
		$data = [
			'album_id' => $this->album5->id,
			'file' => static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			'file_last_modified_time' => 1678824303000,
			'file_name' => TestConstants::SAMPLE_FILE_NIGHT_IMAGE,
			'uuid_name' => '',
			'extension' => '',
			'chunk_number' => 1,
			'total_chunks' => 1,
			'title' => 'integrity_upload_1',
		];
		$response = $this->actingAs($this->admin)->upload('Photo', data: $data);
		$this->assertCreated($response);
		$this->touched_photo_ids[] = $response->json('expected_id');

		// 2/4: PhotoController::update().
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo', [
			'photo_id' => $this->photo1->id,
			'title' => 'integrity_update_2',
			'description' => '',
			'tags' => [],
			'license' => 'none',
			'taken_at' => null,
			'upload_date' => '2021-01-01',
			'from_id' => $this->album1->id,
		]);
		$this->assertOk($response);
		$this->touched_photo_ids[] = $this->photo1->id;

		// 3/4: PhotoController::rename().
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo::rename', [
			'photo_id' => $this->photo1b->id,
			'title' => 'integrity_rename_3',
		]);
		$this->assertNoContent($response);
		$this->touched_photo_ids[] = $this->photo1b->id;

		// 4/4: Actions\Renamer\RenamePhotos (bulk batch()->update()).
		$this->requireSe();
		\Configs::set('renamer_enabled', true);
		RenamerRule::factory()
			->owner_id($this->userMayUpload1->id)
			->order(1)
			->rule('integrity_photo_rule')
			->description('Integrity photo rule')
			->needle('OLD_')
			->replacement('NEW_')
			->mode(RenamerModeType::FIRST)
			->state(['is_enabled' => true, 'is_photo_rule' => true, 'is_album_rule' => false])
			->create();

		$photo = Photo::factory()
			->owned_by($this->userMayUpload1)
			->with_title('OLD_4')
			->in($this->album1)
			->create();
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Renamer', [
			'photo_ids' => [$photo->id],
		]);
		$this->assertNoContent($response);
		$this->touched_photo_ids[] = $photo->id;

		\Configs::set('renamer_enabled', false);
		$this->resetSe();
	}

	private function exerciseAlbumWriteSites(): void
	{
		// 1/8: Actions\Album\Create.
		$response = $this->actingAs($this->userMayUpload1)->postJson('Album', [
			'parent_id' => null,
			'title' => 'integrity_created_album_5',
		]);
		self::assertEquals(200, $response->getStatusCode());
		$this->touched_album_ids[] = $response->getOriginalContent();

		// 2/8: Actions\Album\CreateTagAlbum.
		$response = $this->actingAs($this->userMayUpload1)->postJson('TagAlbum', [
			'title' => 'integrity_tag_album_6',
			'tags' => ['tag1'],
			'is_and' => '1',
		]);
		$this->assertOk($response);
		$this->touched_album_ids[] = $response->getOriginalContent();

		// 3/8: Actions\Album\CreatePersonAlbum.
		\Configs::set('ai_vision_enabled', '1');
		\Configs::set('ai_vision_face_enabled', '1');
		$person = Person::factory()->create(['name' => 'Integrity Person', 'is_searchable' => true]);
		$response = $this->actingAs($this->userMayUpload1)->postJson('PersonAlbum', [
			'title' => 'integrity_person_album_7',
			'persons' => [$person->id],
			'is_and' => false,
		]);
		$this->assertOk($response);
		$person_album_id = $response->getOriginalContent();
		$this->touched_album_ids[] = $person_album_id;

		// 4/8: Actions\Album\SetHeader (choke-point for AlbumController::updateAlbum()).
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', [
			'album_id' => $this->album1->id,
			'title' => 'integrity_updated_album_8',
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
		$this->touched_album_ids[] = $this->album1->id;

		// 5/8: AlbumController::updateTagAlbum().
		$response = $this->actingAs($this->userMayUpload1)->patchJson('TagAlbum', [
			'album_id' => $this->tagAlbum1->id,
			'title' => 'integrity_updated_tag_album_9',
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
		$this->touched_album_ids[] = $this->tagAlbum1->id;

		// 6/8: AlbumController::updatePersonAlbum().
		$response = $this->actingAs($this->userMayUpload1)->patchJson('PersonAlbum', [
			'album_id' => $person_album_id,
			'title' => 'integrity_updated_person_album_10',
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

		// 7/8: AlbumController::rename().
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album::rename', [
			'album_id' => $this->subAlbum1->id,
			'title' => 'integrity_renamed_album_11',
		]);
		$this->assertNoContent($response);
		$this->touched_album_ids[] = $this->subAlbum1->id;

		// 8/8: Actions\Renamer\RenameAlbums (bulk batch()->update()).
		$this->requireSe();
		\Configs::set('renamer_enabled', true);
		RenamerRule::factory()
			->owner_id($this->userMayUpload1->id)
			->order(1)
			->rule('integrity_album_rule')
			->description('Integrity album rule')
			->needle('OLD_')
			->replacement('NEW_')
			->mode(RenamerModeType::FIRST)
			->state(['is_enabled' => true, 'is_photo_rule' => false, 'is_album_rule' => true])
			->create();

		$album = Album::factory()
			->owned_by($this->userMayUpload1)
			->as_root()
			->with_title('OLD_12')
			->create();
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Renamer', [
			'album_ids' => [$album->id],
		]);
		$this->assertNoContent($response);
		$this->touched_album_ids[] = $album->id;

		\Configs::set('renamer_enabled', false);
		$this->resetSe();
	}
}
