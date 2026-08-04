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
use App\Models\Photo;
use App\Models\Tag;
use App\Models\TagAlbum;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
}
