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

namespace Tests\Unit\Actions\Albums;

use App\Actions\Albums\Top;
use App\Models\Album;
use App\Models\Configs;
use App\Models\PersonAlbum;
use App\Models\TagAlbum;
use App\Models\User;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\AbstractTestCase;

class TopTest extends AbstractTestCase
{
	use DatabaseTransactions;

	protected function setUp(): void
	{
		parent::setUp();
		config(['features.enable-caching' => true]);
		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
	}

	private function countListingTableQueries(\Closure $callback): int
	{
		DB::flushQueryLog();
		DB::enableQueryLog();
		$callback();
		$count = count(array_filter(
			DB::getQueryLog(),
			function (array $q): bool {
				// Identifier quoting is driver-specific (SQLite/Postgres use
				// "double quotes", MySQL/MariaDB use `backticks`) — strip both
				// before matching so this works under any test DB driver.
				// "albums" alone covers albums/base_albums/tag_albums/person_albums.
				return str_contains(str_replace(['"', '`'], '', $q['query']), 'albums');
			}
		));
		DB::flushQueryLog();
		DB::disableQueryLog();

		return $count;
	}

	/**
	 * NFR-053-08: no key collision between the tag-albums, person-albums,
	 * and root-albums queries despite all three sorting by the same
	 * criteria and being keyed only by user id.
	 */
	public function testEachQueryTypeReturnsOnlyItsOwnAlbums(): void
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$tag_album = TagAlbum::factory()->owned_by($user)->create(['title' => 'Same Title']);
		$person_album = new PersonAlbum();
		$person_album->title = 'Same Title';
		$person_album->owner_id = $user->id;
		$person_album->is_and = true;
		$person_album->save();
		$root_album = Album::factory()->as_root()->owned_by($user)->create(['title' => 'Same Title']);

		$dto = resolve(Top::class)->get();

		self::assertCount(1, $dto->tag_albums);
		self::assertSame($tag_album->id, $dto->tag_albums->first()->id);

		self::assertCount(1, $dto->person_albums);
		self::assertSame($person_album->id, $dto->person_albums->first()->id);

		$this->assertTrue($dto->albums->concat($dto->shared_albums ?? collect())->contains(fn (Album $a) => $a->id === $root_album->id));
	}

	public function testCacheHitPerformsNoQueriesAgainstAnyListingTable(): void
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		TagAlbum::factory()->owned_by($user)->create();
		Album::factory()->as_root()->owned_by($user)->create();

		resolve(Top::class)->get();

		$second_call_count = $this->countListingTableQueries(fn () => resolve(Top::class)->get());

		self::assertSame(0, $second_call_count, 'A full cache hit across all four queries must not query any listing table.');
	}

	public function testMixedHitAndMissKeepsTheFourQueriesIndependent(): void
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		TagAlbum::factory()->owned_by($user)->create();
		Album::factory()->as_root()->owned_by($user)->create();

		resolve(Top::class)->get();

		// Evict only the pinned-albums-listing cache entry.
		resolve(ManagedCacheService::class)->forgetTag(resolve(CacheKeyProvider::class)->pinnedAlbumsListingTag());

		$second_call_count = $this->countListingTableQueries(fn () => resolve(Top::class)->get());

		// Only the pinned-albums query (against `albums`/`base_albums`) should
		// re-run; tag_albums/person_albums stay served from cache. Since the
		// pinned query itself also touches `albums`, we can only assert this
		// is strictly less than a fully-uncached call, not zero.
		$fully_uncached_count = $this->countListingTableQueries(function (): void {
			$cache_key_provider = resolve(CacheKeyProvider::class);
			resolve(ManagedCacheService::class)->forgetTag($cache_key_provider->tagAlbumsListingTag());
			resolve(ManagedCacheService::class)->forgetTag($cache_key_provider->personAlbumsListingTag());
			resolve(ManagedCacheService::class)->forgetTag($cache_key_provider->albumChildrenTag(null));
			resolve(ManagedCacheService::class)->forgetTag($cache_key_provider->pinnedAlbumsListingTag());
			resolve(Top::class)->get();
		});

		self::assertGreaterThan(0, $second_call_count);
		self::assertGreaterThan($second_call_count, $fully_uncached_count, 'A mixed hit-and-miss call must run fewer queries than a fully uncached call.');
	}

	public function testCollectionsSurviveASerializationRoundTrip(): void
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		TagAlbum::factory()->owned_by($user)->create(['title' => 'Tag Album']);
		$person_album = new PersonAlbum();
		$person_album->title = 'Person Album';
		$person_album->owner_id = $user->id;
		$person_album->is_and = true;
		$person_album->save();
		Album::factory()->as_root()->owned_by($user)->create(['title' => 'Root Album', 'is_pinned' => true]);

		$dto = resolve(Top::class)->get();

		$restored_tag_albums = unserialize(serialize($dto->tag_albums));
		$restored_person_albums = unserialize(serialize($dto->person_albums));
		$restored_pinned_albums = unserialize(serialize($dto->pinned_albums));
		$restored_albums = unserialize(serialize($dto->albums));

		self::assertSame($dto->tag_albums->first()->title, $restored_tag_albums->first()->title);
		self::assertSame($dto->person_albums->first()->title, $restored_person_albums->first()->title);
		self::assertSame($dto->pinned_albums->first()->title, $restored_pinned_albums->first()->title);
		self::assertSame($dto->albums->first()->title, $restored_albums->first()->title);
	}
}
