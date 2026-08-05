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

namespace Tests\Unit\Jobs;

use App\Enum\SmartAlbumType;
use App\Jobs\RecomputeAlbumUserThumbsJob;
use App\Models\AlbumUserThumb;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\TagAlbum;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use Tests\AbstractTestCase;

/**
 * Note: `resolvePersonThumb()`'s "album found" branch is not exercised here
 * (no PersonAlbum factory exists in this codebase); only its "album gone"
 * early-return is covered, which real data can reach without one.
 */
class RecomputeAlbumUserThumbsJobTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testDeletesCachedThumbWhenTagAlbumIsGone(): void
	{
		$user = User::factory()->create();
		AlbumUserThumb::query()->create([
			'user_id' => $user->id,
			'album_id' => 'gone-tag-album-id-000000',
			'photo_id' => Photo::factory()->owned_by($user)->create()->id,
		]);

		(new RecomputeAlbumUserThumbsJob(RecomputeAlbumUserThumbsJob::KIND_TAG, 'gone-tag-album-id-000000'))->handle();

		self::assertDatabaseMissing('album_user_thumbs', ['album_id' => 'gone-tag-album-id-000000']);
	}

	public function testUpdatesCachedThumbForExistingTagAlbum(): void
	{
		$user = User::factory()->create();
		$tag = Tag::factory()->create(['name' => 'sunset']);
		$photo = Photo::factory()->owned_by($user)->create();
		$photo->tags()->attach($tag->id);
		$tag_album = TagAlbum::factory()->owned_by($user)->of_tags([$tag])->create();

		AlbumUserThumb::query()->create([
			'user_id' => null,
			'album_id' => $tag_album->id,
			'photo_id' => $photo->id,
		]);

		(new RecomputeAlbumUserThumbsJob(RecomputeAlbumUserThumbsJob::KIND_TAG, $tag_album->id))->handle();

		self::assertDatabaseHas('album_user_thumbs', [
			'album_id' => $tag_album->id,
			'user_id' => null,
			'photo_id' => $photo->id,
		]);
	}

	public function testResolvesSmartAlbumThumb(): void
	{
		$user = User::factory()->create();
		Photo::factory()->owned_by($user)->create();

		AlbumUserThumb::query()->create([
			'user_id' => $user->id,
			'album_id' => SmartAlbumType::UNSORTED->value,
			'photo_id' => Photo::factory()->owned_by($user)->create()->id,
		]);

		(new RecomputeAlbumUserThumbsJob(RecomputeAlbumUserThumbsJob::KIND_SMART, SmartAlbumType::UNSORTED->value))->handle();

		self::assertDatabaseHas('album_user_thumbs', [
			'album_id' => SmartAlbumType::UNSORTED->value,
			'user_id' => $user->id,
		]);
	}

	public function testDeletesCachedThumbForUnknownSmartAlbumId(): void
	{
		$user = User::factory()->create();
		AlbumUserThumb::query()->create([
			'user_id' => $user->id,
			'album_id' => 'not-a-real-smart-type',
			'photo_id' => Photo::factory()->owned_by($user)->create()->id,
		]);

		(new RecomputeAlbumUserThumbsJob(RecomputeAlbumUserThumbsJob::KIND_SMART, 'not-a-real-smart-type'))->handle();

		self::assertDatabaseMissing('album_user_thumbs', ['album_id' => 'not-a-real-smart-type']);
	}

	public function testDeletesCachedThumbWhenPersonAlbumIsGone(): void
	{
		$user = User::factory()->create();
		AlbumUserThumb::query()->create([
			'user_id' => $user->id,
			'album_id' => 'gone-person-album-id-000',
			'photo_id' => Photo::factory()->owned_by($user)->create()->id,
		]);

		(new RecomputeAlbumUserThumbsJob(RecomputeAlbumUserThumbsJob::KIND_PERSON, 'gone-person-album-id-000'))->handle();

		self::assertDatabaseMissing('album_user_thumbs', ['album_id' => 'gone-person-album-id-000']);
	}

	public function testDeletesCachedThumbForUnknownAlbumKind(): void
	{
		$user = User::factory()->create();
		AlbumUserThumb::query()->create([
			'user_id' => $user->id,
			'album_id' => 'some-id',
			'photo_id' => Photo::factory()->owned_by($user)->create()->id,
		]);

		(new RecomputeAlbumUserThumbsJob('bogus-kind', 'some-id'))->handle();

		self::assertDatabaseMissing('album_user_thumbs', ['album_id' => 'some-id']);
	}

	public function testDoesNothingWhenNoViewersAreCached(): void
	{
		(new RecomputeAlbumUserThumbsJob(RecomputeAlbumUserThumbsJob::KIND_TAG, 'no-cached-viewers-id'))->handle();
		self::assertTrue(true);
	}

	public function testFailedLogsError(): void
	{
		Log::shouldReceive('channel')->once()->with('jobs')->andReturnSelf();
		Log::shouldReceive('error')->once();

		(new RecomputeAlbumUserThumbsJob(RecomputeAlbumUserThumbsJob::KIND_TAG, 'some-album-id'))
			->failed(new \Exception('boom'));
		self::assertTrue(true);
	}

	public function testSupersededJobLogsWithItsOwnContext(): void
	{
		$job = new RecomputeAlbumUserThumbsJob(RecomputeAlbumUserThumbsJob::KIND_TAG, 'debounce-target-id');
		// A second registration for the same kind+id supersedes the first job.
		new RecomputeAlbumUserThumbsJob(RecomputeAlbumUserThumbsJob::KIND_TAG, 'debounce-target-id');

		Log::shouldReceive('channel')->once()->with('jobs')->andReturnSelf();
		Log::shouldReceive('debug')->once()->with(\Mockery::on(
			fn (string $msg) => str_contains($msg, RecomputeAlbumUserThumbsJob::KIND_TAG . ' album debounce-target-id')
		));

		$method = new \ReflectionMethod(RecomputeAlbumUserThumbsJob::class, 'hasNewerJobQueued');
		self::assertTrue($method->invoke($job));
	}
}
