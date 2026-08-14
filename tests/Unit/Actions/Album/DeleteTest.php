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

namespace Tests\Unit\Actions\Album;

use App\Actions\Album\Delete;
use App\Events\AlbumDeleted;
use App\Events\BaseAlbumRemoved;
use App\Models\Album;
use App\Models\PersonAlbum;
use App\Models\Photo;
use App\Models\TagAlbum;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\AbstractTestCase;

class DeleteTest extends AbstractTestCase
{
	use DatabaseTransactions;

	/**
	 * Test cascading deletion with nested albums and shared photos.
	 *
	 * Setup:
	 * - 5 photos: A, B, C, D, E
	 * - 3 albums: X, Y, Z
	 * - Photo relationships:
	 *   - A and D are in X
	 *   - B and E are in Y
	 *   - A, B, C are in Z
	 * - Album relationships:
	 *   - Y is a child of X (Y is in X)
	 *
	 * When deleting X:
	 * - X album should be deleted (parent)
	 * - Y album should be deleted (child of X)
	 * - D should be deleted (only in X)
	 * - E should be deleted (only in Y, which is being deleted)
	 * - A should NOT be deleted (also in Z)
	 * - B should NOT be deleted (also in Z)
	 * - C should NOT be deleted (only in Z)
	 * - Z album should still exist and contain A, B, C
	 *
	 * @return void
	 */
	public function testDeleteAlbumWithNestedAlbumsAndSharedPhotos(): void
	{
		// Create a user for ownership
		$user = User::factory()->create();

		// Create photos
		$photo_a = Photo::factory()->owned_by($user)->create();
		$photo_b = Photo::factory()->owned_by($user)->create();
		$photo_c = Photo::factory()->owned_by($user)->create();
		$photo_d = Photo::factory()->owned_by($user)->create();
		$photo_e = Photo::factory()->owned_by($user)->create();

		// Create albums
		$album_x = Album::factory()->as_root()->owned_by($user)->create(['title' => 'Album X']);
		$album_y = Album::factory()->children_of($album_x)->owned_by($user)->create(['title' => 'Album Y']);
		$album_z = Album::factory()->as_root()->owned_by($user)->create(['title' => 'Album Z']);

		// Attach photos to albums
		// A and D are in X
		$photo_a->albums()->attach($album_x->id);
		$photo_d->albums()->attach($album_x->id);

		// B and E are in Y
		$photo_b->albums()->attach($album_y->id);
		$photo_e->albums()->attach($album_y->id);

		// A, B, C are in Z
		$photo_a->albums()->attach($album_z->id);
		$photo_b->albums()->attach($album_z->id);
		$photo_c->albums()->attach($album_z->id);

		// Verify initial state
		$this->assertDatabaseHas('albums', ['id' => $album_x->id]);
		$this->assertDatabaseHas('albums', ['id' => $album_y->id]);
		$this->assertDatabaseHas('albums', ['id' => $album_z->id]);
		$this->assertDatabaseHas('photos', ['id' => $photo_a->id]);
		$this->assertDatabaseHas('photos', ['id' => $photo_b->id]);
		$this->assertDatabaseHas('photos', ['id' => $photo_c->id]);
		$this->assertDatabaseHas('photos', ['id' => $photo_d->id]);
		$this->assertDatabaseHas('photos', ['id' => $photo_e->id]);

		// Execute deletion
		$delete_action = new Delete();
		$delete_action->do([$album_x->id]);

		// Verify X album is deleted
		$this->assertDatabaseMissing('albums', ['id' => $album_x->id]);

		// Verify Y album is deleted (child of X)
		$this->assertDatabaseMissing('albums', ['id' => $album_y->id]);

		// Verify Z album still exists
		$this->assertDatabaseHas('albums', ['id' => $album_z->id]);

		// Verify photo D is deleted (only in X)
		$this->assertDatabaseMissing('photos', ['id' => $photo_d->id]);

		// Verify photo E is deleted (only in Y)
		$this->assertDatabaseMissing('photos', ['id' => $photo_e->id]);

		// Verify photo A still exists (also in Z)
		$this->assertDatabaseHas('photos', ['id' => $photo_a->id]);

		// Verify photo B still exists (also in Z)
		$this->assertDatabaseHas('photos', ['id' => $photo_b->id]);

		// Verify photo C still exists (only in Z)
		$this->assertDatabaseHas('photos', ['id' => $photo_c->id]);

		// Verify Z still contains A, B, C
		$this->assertEquals(3, DB::table('photo_album')
			->where('album_id', $album_z->id)
			->count());

		$this->assertDatabaseHas('photo_album', [
			'album_id' => $album_z->id,
			'photo_id' => $photo_a->id,
		]);

		$this->assertDatabaseHas('photo_album', [
			'album_id' => $album_z->id,
			'photo_id' => $photo_b->id,
		]);

		$this->assertDatabaseHas('photo_album', [
			'album_id' => $album_z->id,
			'photo_id' => $photo_c->id,
		]);
	}

	public function testDeletePureTagAlbumBatchDispatchesBaseAlbumRemoved(): void
	{
		Event::fake([BaseAlbumRemoved::class]);

		$user = User::factory()->create();
		$tag_album_1 = TagAlbum::factory()->owned_by($user)->create();
		$tag_album_2 = TagAlbum::factory()->owned_by($user)->create();

		(new Delete())->do([$tag_album_1->id, $tag_album_2->id]);

		$this->assertDatabaseMissing('tag_albums', ['id' => $tag_album_1->id]);
		$this->assertDatabaseMissing('tag_albums', ['id' => $tag_album_2->id]);
		Event::assertDispatched(BaseAlbumRemoved::class, fn (BaseAlbumRemoved $e) => in_array($tag_album_1->id, $e->base_album_ids, true) &&
			in_array($tag_album_2->id, $e->base_album_ids, true));
		Event::assertDispatchedTimes(BaseAlbumRemoved::class, 1);
	}

	public function testDeletePersonAlbumDispatchesBaseAlbumRemoved(): void
	{
		Event::fake([BaseAlbumRemoved::class]);

		$user = User::factory()->create();
		$person_album = new PersonAlbum();
		$person_album->title = 'Test Person Album';
		$person_album->owner_id = $user->id;
		$person_album->is_and = true;
		$person_album->save();

		(new Delete())->do([$person_album->id]);

		$this->assertDatabaseMissing('person_albums', ['id' => $person_album->id]);
		Event::assertDispatched(BaseAlbumRemoved::class, fn (BaseAlbumRemoved $e) => in_array($person_album->id, $e->base_album_ids, true));
	}

	public function testDeleteMixedBatchOfRegularAndTagAlbumsDispatchesBothEvents(): void
	{
		Event::fake([BaseAlbumRemoved::class, AlbumDeleted::class]);

		$user = User::factory()->create();
		$parent_album = Album::factory()->as_root()->owned_by($user)->create();
		$regular_album = Album::factory()->children_of($parent_album)->owned_by($user)->create();
		$tag_album = TagAlbum::factory()->owned_by($user)->create();

		(new Delete())->do([$regular_album->id, $tag_album->id]);

		$this->assertDatabaseMissing('albums', ['id' => $regular_album->id]);
		$this->assertDatabaseMissing('tag_albums', ['id' => $tag_album->id]);
		Event::assertDispatched(BaseAlbumRemoved::class, fn (BaseAlbumRemoved $e) => in_array($tag_album->id, $e->base_album_ids, true));
		Event::assertDispatched(AlbumDeleted::class);
	}
}
