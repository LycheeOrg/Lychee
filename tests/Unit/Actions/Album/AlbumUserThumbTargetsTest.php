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

use App\Actions\Album\AlbumUserThumbTargets;
use App\Actions\Album\CreatePersonAlbum;
use App\Enum\SmartAlbumType;
use App\Jobs\RecomputeAlbumUserThumbsJob;
use App\Models\AlbumUserThumb;
use App\Models\Face;
use App\Models\Person;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\TagAlbum;
use App\Models\User;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Tests\AbstractTestCase;

/**
 * FR-112-08 / FR-112-09, S-112-17: the thumb-target resolver shared by
 * {@link \App\Listeners\RecomputeAlbumUserThumbsOnPhotoChange} and the photo
 * delete action.
 */
class AlbumUserThumbTargetsTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private User $user;

	public function setUp(): void
	{
		parent::setUp();
		$this->user = User::factory()->may_upload()->create();
	}

	public function testEmptyInputResolvesNothingWithoutQuery(): void
	{
		$queries = 0;
		DB::listen(function () use (&$queries): void {
			$queries++;
		});

		self::assertSame([], (new AlbumUserThumbTargets())->resolve([]));
		self::assertSame(0, $queries);
	}

	public function testEverySmartAlbumIsResolved(): void
	{
		$photo = Photo::factory()->owned_by($this->user)->create();

		$targets = (new AlbumUserThumbTargets())->resolve([$photo->id]);

		foreach (SmartAlbumType::cases() as $smart_album_type) {
			self::assertContains([RecomputeAlbumUserThumbsJob::KIND_SMART, $smart_album_type->value], $targets);
		}
	}

	public function testTagAlbumSharingATagIsResolved(): void
	{
		$tag = Tag::factory()->with_name('shared')->create();
		$other_tag = Tag::factory()->with_name('other')->create();
		$photo = Photo::factory()->owned_by($this->user)->with_tags([$tag])->create();
		$matching = TagAlbum::factory()->owned_by($this->user)->of_tags([$tag])->create();
		$unrelated = TagAlbum::factory()->owned_by($this->user)->of_tags([$other_tag])->create();

		$targets = (new AlbumUserThumbTargets())->resolve([$photo->id]);

		self::assertContains([RecomputeAlbumUserThumbsJob::KIND_TAG, $matching->id], $targets);
		self::assertNotContains([RecomputeAlbumUserThumbsJob::KIND_TAG, $unrelated->id], $targets);
	}

	public function testPersonAlbumSharingANonDismissedPersonIsResolved(): void
	{
		$photo = Photo::factory()->owned_by($this->user)->create();
		$person = Person::factory()->create();
		$dismissed_person = Person::factory()->create();
		Face::factory()->for_photo($photo)->for_person($person)->create();
		Face::factory()->for_photo($photo)->for_person($dismissed_person)->dismissed()->create();
		$matching = $this->createPersonAlbum([$person->id]);
		$dismissed = $this->createPersonAlbum([$dismissed_person->id]);

		$targets = (new AlbumUserThumbTargets())->resolve([$photo->id]);

		self::assertContains([RecomputeAlbumUserThumbsJob::KIND_PERSON, $matching], $targets);
		self::assertNotContains([RecomputeAlbumUserThumbsJob::KIND_PERSON, $dismissed], $targets);
	}

	public function testAlbumsCachingThePhotoAreResolved(): void
	{
		$photo = Photo::factory()->owned_by($this->user)->create();
		$tag_album = TagAlbum::factory()->owned_by($this->user)->of_tags([Tag::factory()->with_name('unused')->create()])->create();
		$person_album = $this->createPersonAlbum([Person::factory()->create()->id]);
		$this->cacheThumb($tag_album->id, $photo);
		$this->cacheThumb($person_album, $photo);
		$this->cacheThumb(SmartAlbumType::RECENT->value, $photo);

		$targets = (new AlbumUserThumbTargets())->resolve([$photo->id]);

		self::assertContains([RecomputeAlbumUserThumbsJob::KIND_TAG, $tag_album->id], $targets);
		self::assertContains([RecomputeAlbumUserThumbsJob::KIND_PERSON, $person_album], $targets);
		self::assertSame(count($targets), count(array_unique(array_map(fn (array $t) => $t[0] . '|' . $t[1], $targets))));
	}

	public function testTargetsAreDeduplicated(): void
	{
		$tag = Tag::factory()->with_name('shared')->create();
		$photo_a = Photo::factory()->owned_by($this->user)->with_tags([$tag])->create();
		$photo_b = Photo::factory()->owned_by($this->user)->with_tags([$tag])->create();
		$tag_album = TagAlbum::factory()->owned_by($this->user)->of_tags([$tag])->create();
		$this->cacheThumb($tag_album->id, $photo_a);

		$targets = (new AlbumUserThumbTargets())->resolve([$photo_a->id, $photo_b->id, $photo_a->id]);

		$tag_targets = array_filter($targets, fn (array $t) => $t === [RecomputeAlbumUserThumbsJob::KIND_TAG, $tag_album->id]);
		self::assertCount(1, $tag_targets);
		self::assertCount(count(SmartAlbumType::cases()), array_filter($targets, fn (array $t) => $t[0] === RecomputeAlbumUserThumbsJob::KIND_SMART));
	}

	public function testJobsAndDispatchCoverEveryTarget(): void
	{
		Bus::fake();
		$targets = [
			[RecomputeAlbumUserThumbsJob::KIND_SMART, SmartAlbumType::RECENT->value],
			[RecomputeAlbumUserThumbsJob::KIND_TAG, 'tag-album-id-000000000000'],
		];
		$resolver = new AlbumUserThumbTargets();

		$jobs = $resolver->jobs($targets);
		self::assertSame($targets, array_map(fn (RecomputeAlbumUserThumbsJob $job) => [$job->album_kind, $job->album_id], $jobs));

		$resolver->dispatch($targets);
		Bus::assertDispatchedTimes(RecomputeAlbumUserThumbsJob::class, 2);
		Bus::assertDispatched(RecomputeAlbumUserThumbsJob::class, fn (RecomputeAlbumUserThumbsJob $job) => $job->album_id === 'tag-album-id-000000000000');
	}

	/**
	 * S-112-17: 1200 photo ids, the match is in the last chunk, no query binds more than 1000 parameters.
	 */
	public function testLargeInputIsChunked(): void
	{
		$tag = Tag::factory()->with_name('shared')->create();
		$photo = Photo::factory()->owned_by($this->user)->with_tags([$tag])->create();
		$person = Person::factory()->create();
		Face::factory()->for_photo($photo)->for_person($person)->create();
		$tag_album = TagAlbum::factory()->owned_by($this->user)->of_tags([$tag])->create();
		$person_album = $this->createPersonAlbum([$person->id]);
		$cached_tag_album = TagAlbum::factory()->owned_by($this->user)->of_tags([Tag::factory()->with_name('unused')->create()])->create();
		$this->cacheThumb($cached_tag_album->id, $photo);

		$photo_ids = array_map(fn (int $i) => sprintf('missing-photo-%011d', $i), range(1, 1199));
		$photo_ids[] = $photo->id;

		$max_bindings = 0;
		DB::listen(function (QueryExecuted $query) use (&$max_bindings): void {
			$max_bindings = max($max_bindings, count($query->bindings));
		});

		$targets = (new AlbumUserThumbTargets())->resolve($photo_ids);

		self::assertLessThanOrEqual(1000, $max_bindings);
		self::assertContains([RecomputeAlbumUserThumbsJob::KIND_TAG, $tag_album->id], $targets);
		self::assertContains([RecomputeAlbumUserThumbsJob::KIND_PERSON, $person_album], $targets);
		self::assertContains([RecomputeAlbumUserThumbsJob::KIND_TAG, $cached_tag_album->id], $targets);
	}

	/**
	 * @param string[] $person_ids
	 */
	private function createPersonAlbum(array $person_ids): string
	{
		$this->actingAs($this->user);

		return (new CreatePersonAlbum())->create('people', $person_ids, false)->id;
	}

	private function cacheThumb(string $album_id, Photo $photo): void
	{
		AlbumUserThumb::query()->create([
			'user_id' => $this->user->id,
			'album_id' => $album_id,
			'photo_id' => $photo->id,
		]);
	}
}
