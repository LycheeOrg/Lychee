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

use App\Contracts\Models\AbstractAlbum;
use App\Models\Album;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\AlbumQueryPolicy;
use App\Policies\TagPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 118: editing and organising own content depends on `may_edit`,
 * not on `may_upload` (FR-118-09 … 12, S-118-06, S-118-07, S-118-19).
 */
class EditRightTest extends BaseApiWithDataTest
{
	private User $editor;
	private Album $editor_album;
	private Album $editor_child;
	private Album $editor_target;
	private Album $editor_merge_source;
	private Album $editor_to_delete;
	private Photo $editor_photo;
	private Photo $editor_photo_bis;

	public function setUp(): void
	{
		parent::setUp();
		$this->editor = User::factory()->may_edit()->create();
		$this->editor_album = Album::factory()->as_root()->owned_by($this->editor)->create();
		$this->editor_child = Album::factory()->children_of($this->editor_album)->owned_by($this->editor)->create();
		$this->editor_target = Album::factory()->as_root()->owned_by($this->editor)->create();
		$this->editor_merge_source = Album::factory()->as_root()->owned_by($this->editor)->create();
		$this->editor_to_delete = Album::factory()->as_root()->owned_by($this->editor)->create();
		$this->editor_photo = Photo::factory()->owned_by($this->editor)->in($this->editor_album)->create();
		$this->editor_photo_bis = Photo::factory()->owned_by($this->editor)->in($this->editor_album)->create();
	}

	public function tearDown(): void
	{
		$this->resetSe();
		parent::tearDown();
	}

	/**
	 * `userMayUpload1` with the edit right withdrawn directly in the database:
	 * the policies read `may_edit` alone.
	 */
	private function uploaderWithoutEdit(): User
	{
		DB::table('users')->where('id', '=', $this->userMayUpload1->id)->update(['may_edit' => false]);

		return $this->userMayUpload1->fresh();
	}

	private function assertParity(User $user): void
	{
		$this->actingAs($user);

		/** @var AlbumQueryPolicy $policy */
		$policy = resolve(AlbumQueryPolicy::class);
		$query = Album::query()->select('albums.id');
		$policy->appendEditableCondition($query, $user);
		$sql_editable = $query->toBase()->pluck('id')->sort()->values()->all();

		$gate_editable = Album::query()->get()
			->filter(fn (Album $a) => Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $a]))
			->pluck('id')->sort()->values()->all();

		self::assertSame($gate_editable, $sql_editable);
	}

	public function testEditorEditsOwnAlbums(): void
	{
		$response = $this->actingAs($this->editor)->patchJson('Album::rename', ['album_id' => $this->editor_album->id, 'title' => 'renamed']);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->editor)->postJson('Album::cover', ['album_id' => $this->editor_album->id, 'photo_id' => $this->editor_photo->id]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->editor)->postJson('Album', ['parent_id' => $this->editor_album->id, 'title' => 'sub']);
		self::assertSame(200, $response->getStatusCode());

		$response = $this->actingAs($this->editor)->postJson('Album', ['parent_id' => null, 'title' => 'root']);
		self::assertSame(200, $response->getStatusCode());

		$response = $this->actingAs($this->editor)->postJson('TagAlbum', ['title' => 'tags', 'tags' => ['tag1'], 'is_and' => '1']);
		$this->assertOk($response);

		$response = $this->actingAs($this->editor)->postJson('Album::move', ['album_id' => $this->editor_target->id, 'album_ids' => [$this->editor_child->id]]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->editor)->postJson('Album::merge', ['album_id' => $this->editor_target->id, 'album_ids' => [$this->editor_merge_source->id]]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->editor)->deleteJson('Album', ['album_ids' => [$this->editor_to_delete->id]]);
		$this->assertNoContent($response);
	}

	public function testEditorEditsOwnPhotos(): void
	{
		$response = $this->actingAs($this->editor)->patchJson('Photo::rename', ['photo_id' => $this->editor_photo->id, 'title' => 'renamed']);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->editor)->patchJson('Photo::tags', ['photo_ids' => [$this->editor_photo->id, $this->editor_photo_bis->id], 'tags' => ['tag1'], 'shall_override' => false]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->editor)->postJson('Photo::move', ['photo_ids' => [$this->editor_photo->id], 'album_id' => $this->editor_target->id, 'from_id' => $this->editor_album->id]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->editor)->deleteJson('Photo', ['photo_ids' => [$this->editor_photo_bis->id], 'from_id' => $this->editor_album->id]);
		$this->assertNoContent($response);
	}

	public function testEditorEditsTagsAndRenamerRules(): void
	{
		self::assertTrue(Gate::forUser($this->editor)->check(TagPolicy::CAN_EDIT, [Tag::class]));

		$this->requireSe();
		$response = $this->actingAs($this->editor)->getJson('Renamer');
		$this->assertOk($response);
	}

	public function testEditorRights(): void
	{
		$this->actingAs($this->editor);
		self::assertTrue(Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $this->editor_album]));
		self::assertTrue(Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, null]));
		self::assertFalse(Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, null]));
		$this->assertParity($this->editor);
	}

	public function testUserWithoutEditRightIsRefused(): void
	{
		$uploader = $this->uploaderWithoutEdit();

		$response = $this->actingAs($uploader)->patchJson('Album::rename', ['album_id' => $this->album1->id, 'title' => 'renamed']);
		$this->assertForbidden($response);

		$response = $this->actingAs($uploader)->postJson('Album::cover', ['album_id' => $this->album1->id, 'photo_id' => $this->photo1->id]);
		$this->assertForbidden($response);

		$response = $this->actingAs($uploader)->postJson('Album', ['parent_id' => $this->album1->id, 'title' => 'sub']);
		$this->assertForbidden($response);

		$response = $this->actingAs($uploader)->postJson('Album', ['parent_id' => null, 'title' => 'root']);
		$this->assertForbidden($response);

		$response = $this->actingAs($uploader)->postJson('TagAlbum', ['title' => 'tags', 'tags' => ['tag1'], 'is_and' => '1']);
		$this->assertForbidden($response);

		$response = $this->actingAs($uploader)->postJson('Album::move', ['album_id' => null, 'album_ids' => [$this->subAlbum1->id]]);
		$this->assertForbidden($response);

		$response = $this->actingAs($uploader)->deleteJson('Album', ['album_ids' => [$this->subAlbum1->id]]);
		$this->assertForbidden($response);

		$response = $this->actingAs($uploader)->patchJson('Photo::rename', ['photo_id' => $this->photo1->id, 'title' => 'renamed']);
		$this->assertForbidden($response);

		$response = $this->actingAs($uploader)->patchJson('Photo::tags', ['photo_ids' => [$this->photo1->id], 'tags' => ['tag1'], 'shall_override' => false]);
		$this->assertForbidden($response);

		$response = $this->actingAs($uploader)->postJson('Photo::move', ['photo_ids' => [$this->photo1->id], 'album_id' => $this->subAlbum1->id, 'from_id' => $this->album1->id]);
		$this->assertForbidden($response);

		$response = $this->actingAs($uploader)->deleteJson('Photo', ['photo_ids' => [$this->photo1->id], 'from_id' => $this->album1->id]);
		$this->assertForbidden($response);

		self::assertFalse(Gate::forUser($uploader)->check(TagPolicy::CAN_EDIT, [Tag::class]));

		$this->requireSe();
		$response = $this->actingAs($uploader)->getJson('Renamer');
		$this->assertForbidden($response);

		$this->actingAs($uploader);
		self::assertFalse(Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $this->album1]));
		$this->assertParity($uploader);
	}

	public function testOwnerWithoutAnyRightCannotEditOwnPhoto(): void
	{
		// `userNoUpload` owns photo3 in album3 but has no edit right (Q-118-08 A).
		$response = $this->actingAs($this->userNoUpload)->patchJson('Photo::rename', ['photo_id' => $this->photo3->id, 'title' => 'renamed']);
		$this->assertForbidden($response);
	}
}
