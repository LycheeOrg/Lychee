<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Sitemap;

use App\Models\Album;
use App\Models\Builders\AlbumBuilder;
use App\Policies\AlbumQueryPolicy;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Support\Facades\Log;

/**
 * Albums listed in the sitemap (Feature 117, FR-117-02 … FR-117-04,
 * FR-117-08, FR-117-10, FR-117-11, NFR-117-01).
 *
 * Regular albums only, seen as a guest whoever asks: visible and browsable
 * from the root with no unlocked album. A private, password-protected or
 * link-only album hides itself and everything below it. Read with toBase():
 * no model is hydrated.
 *
 * The rows are cached per pair of settings under the sitemap tag, which the
 * album-listing invalidator flushes (NFR-117-02, ADR-117-01).
 *
 * @phpstan-import-type SitemapRow from SitemapRenderer
 */
final class ListSitemapAlbums
{
	/** sitemaps.org limit of `<url>` entries in one file, the gallery root included. */
	public const MAX_URLS = 50000;

	public function __construct(
		private readonly AlbumQueryPolicy $album_query_policy,
		private readonly ManagedCacheService $cache,
		private readonly CacheKeyProvider $cache_key_provider,
		private readonly int $max_albums = self::MAX_URLS - 1,
	) {
	}

	/**
	 * @param bool $root_only only the albums at the gallery root
	 * @param bool $hide_nsfw leave out sensitive albums and everything below them
	 *
	 * @return list<SitemapRow> in tree order
	 */
	public function do(bool $root_only, bool $hide_nsfw): array
	{
		return $this->cache->remember(
			$this->cache_key_provider->sitemapKey($root_only, $hide_nsfw),
			[$this->cache_key_provider->sitemapTag(), $this->cache_key_provider->albumListingGlobalTag()],
			fn (): array => $this->query($root_only, $hide_nsfw),
		);
	}

	/**
	 * @return list<SitemapRow>
	 */
	private function query(bool $root_only, bool $hide_nsfw): array
	{
		$query = Album::query()
			->select(['albums.id', 'sitemap_base.slug', 'sitemap_base.updated_at', 'albums.max_taken_at'])
			// The policy's own base_albums join only carries the listing columns.
			->join('base_albums as sitemap_base', 'sitemap_base.id', '=', 'albums.id')
			->when($root_only, fn (AlbumBuilder $q) => $q->whereNull('albums.parent_id'))
			->when($hide_nsfw, fn (AlbumBuilder $q) => $q->where('albums.is_recursive_nsfw', '=', false));
		$query = $this->album_query_policy->applyVisibilityFilter($query, null);
		$query = $this->album_query_policy->applyBrowsabilityFilter($query, null, []);

		$rows = $query
			->orderBy('albums._lft')
			->limit($this->max_albums + 1)
			->toBase()
			->get()
			->map(fn (object $row): array => [
				'id' => $row->id,
				'slug' => $row->slug,
				'updated_at' => $row->updated_at,
				'max_taken_at' => $row->max_taken_at,
			])
			->all();

		return $this->capped($rows);
	}

	/**
	 * @param list<SitemapRow> $rows
	 *
	 * @return list<SitemapRow>
	 */
	private function capped(array $rows): array
	{
		if (count($rows) <= $this->max_albums) {
			return $rows;
		}

		Log::warning('Sitemap truncated to ' . $this->max_albums . ' albums: a sitemap holds at most ' . self::MAX_URLS . ' URLs.');

		return array_slice($rows, 0, $this->max_albums);
	}
}
