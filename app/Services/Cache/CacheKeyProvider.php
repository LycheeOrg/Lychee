<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services\Cache;

use App\DTO\SortingCriterion;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;

/**
 * Single source of truth for every {@see ManagedCacheService} key and tag
 * used by the album/tag listing cache (Feature 053).
 *
 * A producer (e.g. `AlbumRepository`, `Top`, `GetTagWithPhotosAndAlbums`)
 * and its invalidator (e.g. `ManagedCacheAlbumListingInvalidator`) must
 * agree byte-for-byte on the shape of a key or tag, or eviction silently
 * misses. Centralizing the string formats here is what guarantees that
 * agreement instead of relying on copy-pasted interpolation staying in
 * sync across files.
 */
class CacheKeyProvider
{
	// ── Tags ──────────────────────────────────────────────────────
	// A tag groups every cached entry that must be evicted together.
	// Keys below are always tagged with (at least) the tag(s) their
	// content depends on.

	public function albumTag(string $album_id): string
	{
		return "album:{$album_id}";
	}

	/**
	 * @param string[] $album_ids
	 *
	 * @return string[]
	 */
	public function albumTags(array $album_ids): array
	{
		return array_map($this->albumTag(...), $album_ids);
	}

	/**
	 * @param ?string $parent_id `null` denotes the root album listing
	 */
	public function albumChildrenTag(?string $parent_id): string
	{
		$parent_id ??= 'root';

		return "album-children:{$parent_id}";
	}

	/**
	 * @param (string|null)[] $parent_ids
	 *
	 * @return string[]
	 */
	public function albumChildrenTags(array $parent_ids): array
	{
		return array_map($this->albumChildrenTag(...), $parent_ids);
	}

	/**
	 * @param int|string|null $user_id `null` denotes the anonymous/guest visitor
	 */
	public function userTag(int|string|null $user_id): string
	{
		$user_id ??= 'guest';

		return "user:{$user_id}";
	}

	/**
	 * Coarse tag carried by every cached entry across all six listing
	 * query types, in addition to its own specific tag(s); evicting it
	 * alone is sufficient to flush the whole album-listing cache.
	 */
	public function albumListingGlobalTag(): string
	{
		return 'album-listing-global';
	}

	public function tagAlbumsListingTag(): string
	{
		return 'tag-albums-listing';
	}

	public function personAlbumsListingTag(): string
	{
		return 'person-albums-listing';
	}

	public function pinnedAlbumsListingTag(): string
	{
		return 'pinned-albums-listing';
	}

	public function albumTagTag(int $tag_id): string
	{
		return "tag:{$tag_id}";
	}

	/**
	 * @param int[] $tag_ids
	 *
	 * @return string[]
	 */
	public function albumTagTags(array $tag_ids): array
	{
		return array_map($this->albumTagTag(...), $tag_ids);
	}

	// ── Keys ──────────────────────────────────────────────────────
	// A key identifies one memoized value.

	public function albumChildrenPageKey(?string $parent_id, int|string|null $user_id, int $page, int $per_page, SortingCriterion $sorting): string
	{
		$album_children_tag = $this->albumChildrenTag($parent_id);
		$user_tag = $this->userTag($user_id);

		return "{$album_children_tag}:{$user_tag}:page:{$page}:per_page:{$per_page}:sort:{$sorting->column->value}:{$sorting->order->value}";
	}

	public function rootAlbumsListingKey(int|string|null $user_id, SortingCriterion $sorting): string
	{
		$album_children_tag = $this->albumChildrenTag(null);
		$user_tag = $this->userTag($user_id);

		return "{$album_children_tag}:{$user_tag}:sort:{$sorting->column->value}:{$sorting->order->value}";
	}

	public function tagAlbumsListingKey(int|string|null $user_id, SortingCriterion $sorting): string
	{
		$tag_albums_listing_tag = $this->tagAlbumsListingTag();
		$user_tag = $this->userTag($user_id);

		return "{$tag_albums_listing_tag}:{$user_tag}:sort:{$sorting->column->value}:{$sorting->order->value}";
	}

	public function personAlbumsListingKey(int|string|null $user_id, SortingCriterion $sorting): string
	{
		$person_albums_listing_tag = $this->personAlbumsListingTag();
		$user_tag = $this->userTag($user_id);

		return "{$person_albums_listing_tag}:{$user_tag}:sort:{$sorting->column->value}:{$sorting->order->value}";
	}

	public function pinnedAlbumsListingKey(int|string|null $user_id, ?ColumnSortingType $column, ?OrderSortingType $order): string
	{
		$pinned_albums_listing_tag = $this->pinnedAlbumsListingTag();
		$user_tag = $this->userTag($user_id);
		$column_value = $column?->value ?? 'null';
		$order_value = $order?->value ?? 'null';

		return "{$pinned_albums_listing_tag}:{$user_tag}:sort:{$column_value}:{$order_value}";
	}

	/**
	 * @param string $unlocked_digest session-scoped digest of currently-unlocked album ids; see {@see \App\Actions\Tag\GetTagWithPhotosAndAlbums}
	 */
	public function tagAlbumsKey(int $tag_id, int|string|null $user_id, string $unlocked_digest): string
	{
		$user_tag = $this->userTag($user_id);

		return "tag-albums:{$tag_id}:{$user_tag}:unlocked:{$unlocked_digest}";
	}
}
