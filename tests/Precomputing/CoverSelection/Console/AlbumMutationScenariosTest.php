<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Precomputing\CoverSelection\Console;

use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Test album mutation scenarios (S-003-01 through S-003-11).
 *
 * Verifies that album computed fields update correctly in response to:
 * - Photo uploads, deletions, date changes
 * - Album creation, moves, deletions
 * - Starring photos
 * - Nested album mutations
 */
class AlbumMutationScenariosTest extends BasePrecomputingTest
{
	/**
	 * S-003-01: Upload photo to empty album.
	 */
	public function testUploadPhotoToEmptyAlbum(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		// Initially empty
		$this->assertEquals(0, $album->num_photos);
		$this->assertNull($album->min_taken_at);
		$this->assertNull($album->max_taken_at);

		// Upload photo
		$photo = Photo::factory()->owned_by($user)->create([
			'taken_at' => new Carbon('2023-06-15 10:00:00', 'UTC'),
		]);
		$photo->albums()->attach($album->id);

		// Trigger recomputation (in real app, event listener does this)
		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();

		$this->assertEquals(1, $album->num_photos);
		$this->assertEquals('2023-06-15 10:00:00', $album->min_taken_at->format('Y-m-d H:i:s'));
		$this->assertEquals('2023-06-15 10:00:00', $album->max_taken_at->format('Y-m-d H:i:s'));
	}

	/**
	 * S-003-02: Delete last photo from album.
	 */
	public function testDeleteLastPhotoFromAlbum(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$photo = Photo::factory()->owned_by($user)->create([
			'taken_at' => new Carbon('2023-06-15 10:00:00', 'UTC'),
		]);
		$photo->albums()->attach($album->id);

		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();
		$this->assertEquals(1, $album->num_photos);

		// Delete photo
		$photo->albums()->detach($album->id);

		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();

		$this->assertEquals(0, $album->num_photos);
		$this->assertNull($album->min_taken_at);
		$this->assertNull($album->max_taken_at);
	}

	/**
	 * S-003-03: Upload photo with older taken_at.
	 */
	public function testUploadPhotoWithOlderTakenAt(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$photo1 = Photo::factory()->owned_by($user)->create([
			'taken_at' => new Carbon('2023-06-15 10:00:00', 'UTC'),
		]);
		$photo1->albums()->attach($album->id);

		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();
		$this->assertEquals('2023-06-15 10:00:00', $album->min_taken_at->format('Y-m-d H:i:s'));
		$this->assertEquals('2023-06-15 10:00:00', $album->max_taken_at->format('Y-m-d H:i:s'));

		// Upload older photo
		$photo2 = Photo::factory()->owned_by($user)->create([
			'taken_at' => new Carbon('2023-01-10 08:00:00', 'UTC'),
		]);
		$photo2->albums()->attach($album->id);

		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();

		$this->assertEquals(2, $album->num_photos);
		$this->assertEquals('2023-01-10 08:00:00', $album->min_taken_at->format('Y-m-d H:i:s'));
		$this->assertEquals('2023-06-15 10:00:00', $album->max_taken_at->format('Y-m-d H:i:s'));
	}

	/**
	 * S-003-04: Upload photo with newer taken_at.
	 */
	public function testUploadPhotoWithNewerTakenAt(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$photo1 = Photo::factory()->owned_by($user)->create([
			'taken_at' => new Carbon('2023-06-15 10:00:00', 'UTC'),
		]);
		$photo1->albums()->attach($album->id);

		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();
		$this->assertEquals('2023-06-15 10:00:00', $album->min_taken_at->format('Y-m-d H:i:s'));
		$this->assertEquals('2023-06-15 10:00:00', $album->max_taken_at->format('Y-m-d H:i:s'));

		// Upload newer photo
		$photo2 = Photo::factory()->owned_by($user)->create([
			'taken_at' => new Carbon('2023-12-25 18:00:00', 'UTC'),
		]);
		$photo2->albums()->attach($album->id);

		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();

		$this->assertEquals(2, $album->num_photos);
		$this->assertEquals('2023-06-15 10:00:00', $album->min_taken_at->format('Y-m-d H:i:s'));
		$this->assertEquals('2023-12-25 18:00:00', $album->max_taken_at->format('Y-m-d H:i:s'));
	}

	/**
	 * S-003-05: Create child album.
	 */
	public function testCreateChildAlbum(): void
	{
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();

		$this->assertEquals(0, $parent->num_children);

		// Create child
		$child = Album::factory()->owned_by($user)->create();
		$child->appendToNode($parent)->save();

		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $parent->id,
			'--sync' => true,
		]);

		$parent->refresh();

		$this->assertEquals(1, $parent->num_children);
	}

	/**
	 * S-003-06: Move album to different parent.
	 */
	public function testMoveAlbumToDifferentParent(): void
	{
		$user = User::factory()->create();
		$parent1 = Album::factory()->as_root()->owned_by($user)->create(['title' => 'Parent 1']);
		$parent2 = Album::factory()->as_root()->owned_by($user)->create(['title' => 'Parent 2']);

		$child = Album::factory()->owned_by($user)->create(['title' => 'Child']);
		$child->appendToNode($parent1)->save();

		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $parent1->id,
			'--sync' => true,
		]);

		$parent1->refresh();
		$this->assertEquals(1, $parent1->num_children);

		// Move child to parent2
		$child->appendToNode($parent2)->save();

		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $parent1->id,
			'--sync' => true,
		]);
		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $parent2->id,
			'--sync' => true,
		]);

		$parent1->refresh();
		$parent2->refresh();

		$this->assertEquals(0, $parent1->num_children);
		$this->assertEquals(1, $parent2->num_children);
	}

	/**
	 * S-003-07: Delete child album.
	 */
	public function testDeleteChildAlbum(): void
	{
		$user = User::factory()->create();
		$parent = Album::factory()->as_root()->owned_by($user)->create();
		$child = Album::factory()->owned_by($user)->create();
		$child->appendToNode($parent)->save();

		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $parent->id,
			'--sync' => true,
		]);

		$parent->refresh();
		$this->assertEquals(1, $parent->num_children);

		// Delete child
		$child->delete();

		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $parent->id,
			'--sync' => true,
		]);

		$parent->refresh();

		$this->assertEquals(0, $parent->num_children);
	}

	/**
	 * S-003-08: Star photo in album.
	 */
	public function testStarPhotoInAlbum(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		$photo1 = Photo::factory()->owned_by($user)->create([
			'is_highlighted' => false,
			'taken_at' => new Carbon('2023-12-31 10:00:00', 'UTC'),
		]);
		$photo2 = Photo::factory()->owned_by($user)->create([
			'is_highlighted' => false,
			'taken_at' => new Carbon('2023-01-31 10:00:00', 'UTC'),
		]);

		$photo1->albums()->attach($album->id);
		$photo2->albums()->attach($album->id);

		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();
		$oldCover = $album->auto_cover_id_max_privilege;

		// Star the older photo
		$photo1->is_highlighted = true;
		$photo1->save();

		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $album->id,
			'--sync' => true,
		]);

		$album->refresh();

		// Cover should change to highlighted photo (highlighted takes priority over taken_at)
		$this->assertNotEquals($oldCover, $album->auto_cover_id_max_privilege);
		$this->assertEquals($photo1->id, $album->auto_cover_id_max_privilege);
	}

	/**
	 * S-003-11: Nested album mutation propagates to ancestors.
	 */
	public function testNestedAlbumMutationPropagates(): void
	{
		$user = User::factory()->create();

		// Create 3-level hierarchy
		$root = Album::factory()->as_root()->owned_by($user)->create(['title' => 'Root']);
		$child = Album::factory()->owned_by($user)->create(['title' => 'Child']);
		$child->appendToNode($root)->save();
		$grandchild = Album::factory()->owned_by($user)->create(['title' => 'Grandchild']);
		$grandchild->appendToNode($child)->save();

		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $grandchild->id,
			'--sync' => true,
		]);

		$root->refresh();
		$this->assertNull($root->min_taken_at);

		// Add photo to grandchild
		$photo = Photo::factory()->owned_by($user)->create([
			'taken_at' => new Carbon('2023-06-15 10:00:00', 'UTC'),
		]);
		$photo->albums()->attach($grandchild->id);

		// Recompute from grandchild up
		Artisan::call('lychee:recompute-album-stats', [
			'album_id' => $grandchild->id,
			'--sync' => true,
		]);

		// Root should now reflect photo from descendant
		$root->refresh();
		$child->refresh();
		$grandchild->refresh();

		$this->assertEquals(1, $grandchild->num_photos);
		$this->assertEquals(0, $child->num_photos); // Direct photos only
		$this->assertEquals(0, $root->num_photos); // Direct photos only

		// But date range includes descendants
		$this->assertEquals('2023-06-15 10:00:00', $root->min_taken_at->format('Y-m-d H:i:s'));
		$this->assertEquals('2023-06-15 10:00:00', $root->max_taken_at->format('Y-m-d H:i:s'));
	}
}
