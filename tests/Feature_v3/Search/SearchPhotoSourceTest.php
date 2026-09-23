<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v3\Search;

use App\Actions\Search\PhotoSearch;
use App\Actions\Search\SearchTokenParser;
use App\Actions\Search\StructOfArrays\SearchPhotoSource;
use App\Constants\PhotoAlbum as PA;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 069, I2 — the search photo source: dedup (S-069-08), per-photo
 * `album_ids` collapse (S-069-11), origin-album subtree scoping (S-069-12) and
 * NSFW gating (S-069-15).
 *
 * These exercise the query construction directly rather than through HTTP, so
 * a failure points at the query rather than at routing/serialization.
 */
class SearchPhotoSourceTest extends BaseApiWithDataTest
{
	private SearchPhotoSource $source;

	public function setUp(): void
	{
		parent::setUp();
		$this->source = resolve(SearchPhotoSource::class);
	}

	public function tearDown(): void
	{
		Auth::logout();
		parent::tearDown();
	}

	/**
	 * @return array<int,\App\DTO\Search\SearchToken>
	 */
	private function tokens(string $raw): array
	{
		return SearchTokenParser::parse($raw);
	}

	/**
	 * @return string[]
	 */
	private function idsFor(string $raw, ?\App\Models\Album $origin = null): array
	{
		$rows = $this->source->query($this->tokens($raw), $origin)->select('photos.id')->toBase()->get();

		return $rows->pluck('id')->all();
	}

	// ── S-069-08 — dedup ────────────────────────────────────────────

	public function testPhotoBelongingToThreeAlbumsIsReturnedExactlyOnce(): void
	{
		Auth::login($this->admin);

		// photo1 already belongs to album1; add it to two more.
		DB::table(PA::PHOTO_ALBUM)->insert([
			['photo_id' => $this->photo1->id, 'album_id' => $this->album2->id],
			['photo_id' => $this->photo1->id, 'album_id' => $this->subAlbum1->id],
		]);

		$ids = $this->idsFor($this->photo1->title);

		self::assertSame(1, count(array_keys($ids, $this->photo1->id, true)));

		// Teeth for the assertion above: the v2 query this one is derived from
		// really does fan out to one row per album membership (Q-069-08), so a
		// regression that dropped the dedup would be caught here rather than
		// passing vacuously.
		$v2_rows = resolve(PhotoSearch::class)
			->sqlQuery($this->tokens($this->photo1->title), null, with_relations: false)
			->select('photos.id')->toBase()->get()->pluck('id')->all();
		self::assertSame(3, count(array_keys($v2_rows, $this->photo1->id, true)));
	}

	// ── S-069-11 — album_ids collapse ───────────────────────────────

	public function testAlbumIdsResolvesOneAccessibleAlbumPerPhoto(): void
	{
		Auth::login($this->userMayUpload1);

		$album_ids = $this->source->resolveAlbumIds([$this->photo1->id], null, $this->userMayUpload1);

		self::assertArrayHasKey($this->photo1->id, $album_ids);
		self::assertSame($this->album1->id, $album_ids[$this->photo1->id]);
	}

	public function testAlbumIdsSkipsAnAlbumTheViewerCannotAccess(): void
	{
		// photo1 lives in album1 (owned by userMayUpload1). Also place it in
		// album3 (owned by userNoUpload, not shared). For userMayUpload1 the
		// only accessible containing album is album1, whichever id sorts lower.
		DB::table(PA::PHOTO_ALBUM)->insert([
			['photo_id' => $this->photo1->id, 'album_id' => $this->album3->id],
		]);

		Auth::login($this->userMayUpload1);

		$album_ids = $this->source->resolveAlbumIds([$this->photo1->id], null, $this->userMayUpload1);

		self::assertSame($this->album1->id, $album_ids[$this->photo1->id]);
	}

	/**
	 * `applySearchabilityFilter()` admits a photo the viewer owns regardless of
	 * whether any of its containing albums is reachable (`PhotoQueryPolicy`'s
	 * `orWhere('photos.owner_id', ...)` escape). Such a photo can therefore
	 * carry an in-subtree membership the viewer cannot access at all, and
	 * `album_ids[i]` must not name it (FR-069-04) — it feeds the `{album_id}`
	 * segment of the Asset URL, which would then 403 on every thumbnail.
	 */
	public function testAlbumIdsSkipsAnInaccessibleSubtreeAlbumEvenWhenItSortsFirst(): void
	{
		// Created first, so it takes the lower `_lft` of the two and would win
		// a pure `_lft` tie-break.
		$foreign = Album::factory()->children_of($this->album1)->owned_by($this->userMayUpload1)->create();
		$own = Album::factory()->children_of($this->album1)->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($own)->create();
		DB::table(PA::PHOTO_ALBUM)->insert([
			['photo_id' => $photo->id, 'album_id' => $foreign->id],
		]);
		// Written last: `children_of()` copies the parent's owner, and the
		// nested-set writes that follow each further album/photo creation
		// rewrite the row again.
		DB::table('base_albums')->where('id', '=', $foreign->id)->update(['owner_id' => $this->userNoUpload->id]);

		Auth::login($this->userMayUpload1);

		$album_ids = $this->source->resolveAlbumIds([$photo->id], $this->album1, $this->userMayUpload1);

		self::assertSame($own->id, $album_ids[$photo->id]);
	}

	// ── S-069-12 — origin subtree scoping ───────────────────────────

	public function testOriginAlbumRestrictsMatchesToItsSubtree(): void
	{
		Auth::login($this->userMayUpload1);

		// Unscoped, photo1's own title matches it.
		self::assertContains($this->photo1->id, $this->idsFor($this->photo1->title));

		// Scoped to album2, photo1 (which lives in album1) must disappear.
		self::assertNotContains($this->photo1->id, $this->idsFor($this->photo1->title, $this->album2));
	}

	public function testOriginAlbumIncludesItsOwnSubAlbums(): void
	{
		Auth::login($this->userMayUpload1);

		$scoped = $this->idsFor($this->subPhoto1->title, $this->album1);

		self::assertContains($this->subPhoto1->id, $scoped);
	}

	// ── S-069-15 — NSFW gating ──────────────────────────────────────

	public function testNsfwPhotosAreExcludedWhenHideNsfwInSearchIsOn(): void
	{
		$this->album1->is_nsfw = true;
		$this->album1->save();
		Configs::set('hide_nsfw_in_search', '1');
		Auth::login($this->userMayUpload1);

		self::assertNotContains($this->photo1->id, $this->idsFor($this->photo1->title));
	}

	/**
	 * Split from the test above rather than toggling the config mid-test:
	 * `PhotoSearch` holds an injected `ConfigManager`, so a value re-read after
	 * the source has already been resolved is not guaranteed to be observed.
	 */
	public function testNsfwPhotosAreIncludedWhenHideNsfwInSearchIsOff(): void
	{
		$this->album1->is_nsfw = true;
		$this->album1->save();
		Configs::set('hide_nsfw_in_search', '0');
		Auth::login($this->userMayUpload1);

		self::assertContains($this->photo1->id, $this->idsFor($this->photo1->title));
	}
}
