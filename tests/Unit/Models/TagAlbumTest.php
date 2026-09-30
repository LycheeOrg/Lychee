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

namespace Tests\Unit\Models;

use App\Models\AlbumUserThumb;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\TagAlbum;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\AbstractTestCase;

class TagAlbumTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testThumbUsesExplicitCoverWhenSet(): void
	{
		$user = User::factory()->create();
		$cover_photo = Photo::factory()->owned_by($user)->create();
		$tag_album = TagAlbum::factory()->owned_by($user)->create(['cover_id' => $cover_photo->id]);

		$thumb = $tag_album->thumb;

		self::assertNotNull($thumb);
		self::assertEquals($cover_photo->id, $thumb->id);
	}

	public function testThumbUsesEagerLoadedUserThumbRowWhenPresent(): void
	{
		$user = User::factory()->create();
		$cached_photo = Photo::factory()->owned_by($user)->create();
		$tag_album = TagAlbum::factory()->owned_by($user)->create();

		AlbumUserThumb::query()->create([
			'user_id' => null,
			'album_id' => $tag_album->id,
			'photo_id' => $cached_photo->id,
		]);

		$loaded = TagAlbum::query()->with('userThumbRow.photo.size_variants')->find($tag_album->id);

		self::assertEquals($cached_photo->id, $loaded->thumb->id);
	}

	public function testThumbComputesLiveAndSeedsCacheWhenNoCoverOrCacheRow(): void
	{
		$user = User::factory()->create();
		$tag = Tag::factory()->create(['name' => 'sunset']);
		$photo = Photo::factory()->owned_by($user)->create();
		$photo->tags()->attach($tag->id);
		$tag_album = TagAlbum::factory()->owned_by($user)->of_tags([$tag])->create();

		self::assertDatabaseMissing('album_user_thumbs', ['album_id' => $tag_album->id]);

		$thumb = $tag_album->thumb;

		self::assertNotNull($thumb);
		self::assertEquals($photo->id, $thumb->id);
		self::assertDatabaseHas('album_user_thumbs', ['album_id' => $tag_album->id, 'photo_id' => $photo->id]);
	}

	public function testThumbIsNullWhenNoPhotosMatchTags(): void
	{
		$user = User::factory()->create();
		$tag_album = TagAlbum::factory()->owned_by($user)->create();

		self::assertNull($tag_album->thumb);
	}

	// ── Feature 075 (FR-075-08, S-075-10) ────────────────────────

	public function testThumbSeedsThreeCachedIdsInEffectiveOrder(): void
	{
		Configs::set('sorting_photos_col', 'created_at');
		Configs::set('sorting_photos_order', 'ASC');
		$user = User::factory()->create();
		$tag = Tag::factory()->create(['name' => 'sunset']);
		$tag_album = TagAlbum::factory()->owned_by($user)->of_tags([$tag])->create();
		$photos = [];
		for ($i = 0; $i < 4; $i++) {
			$photo = Photo::factory()->owned_by($user)->create(['created_at' => Carbon::parse('2024-01-01')->addDays($i)]);
			$photo->tags()->attach($tag->id);
			$photos[] = $photo->id;
		}

		$thumb = $tag_album->thumb;

		self::assertSame($photos[0], $thumb?->id);
		self::assertDatabaseHas('album_user_thumbs', [
			'album_id' => $tag_album->id,
			'photo_id' => $photos[0],
			'photo_id_2' => $photos[1],
			'photo_id_3' => $photos[2],
		]);
	}

	public function testThumbSeedsNullSidesWhenOnlyOnePhotoMatches(): void
	{
		$user = User::factory()->create();
		$tag = Tag::factory()->create(['name' => 'sunset']);
		$photo = Photo::factory()->owned_by($user)->create();
		$photo->tags()->attach($tag->id);
		$tag_album = TagAlbum::factory()->owned_by($user)->of_tags([$tag])->create();

		$tag_album->thumb;

		self::assertDatabaseHas('album_user_thumbs', ['album_id' => $tag_album->id, 'photo_id' => $photo->id, 'photo_id_2' => null, 'photo_id_3' => null]);
	}
}
