<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Albums;

use App\Contracts\Exceptions\InternalLycheeException;
use App\DTO\AlbumSortingCriterion;
use App\DTO\TopAlbumDTO;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\InvalidOrderDirectionException;
use App\Factories\AlbumFactory;
use App\Models\Album;
use App\Models\Builders\AlbumBuilder;
use App\Models\Extensions\SortingDecorator;
use App\Models\PersonAlbum;
use App\Models\TagAlbum;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\AlbumQueryPolicy;
use App\Repositories\ConfigManager;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class Top
{
	private AlbumSortingCriterion $sorting;
	private bool $is_cache_albums_enabled;

	/**
	 * @throws InvalidOrderDirectionException
	 * @throws ConfigurationKeyMissingException
	 */
	public function __construct(
		private AlbumFactory $album_factory,
		private AlbumQueryPolicy $album_query_policy,
		protected readonly ConfigManager $config_manager,
		protected readonly ManagedCacheService $managed_cache_service,
		protected readonly CacheKeyProvider $cache_key_provider,
	) {
		$this->sorting = AlbumSortingCriterion::createDefault();
		$this->is_cache_albums_enabled = $this->config_manager->getValueAsBool('managed_cache_albums_enabled');
	}

	/**
	 * Returns the top-level albums (but not tag albums) visible
	 * to the current user.
	 *
	 * If the user is authenticated, then the result differentiates between
	 * albums which are owned by the user and "shared" albums which the
	 * user does not own, but is allowed to see.
	 * The term "shared album" might be a little misleading here.
	 * Albums which are owned by the user himself may also be shared (with
	 * other users.)
	 * Actually, in this context "shared albums" means "foreign albums".
	 *
	 * Note, the result may include password-protected albums that are not
	 * accessible (but are visible).
	 *
	 * @return TopAlbumDTO
	 *
	 * @throws InternalLycheeException
	 */
	public function get(): TopAlbumDTO
	{
		/** @var ?User $user */
		$user = Auth::user();
		$user_id = $user?->id;

		// Do not eagerly load the relation `photos` for each smart album.
		// On the albums overview, we only need a thumbnail for each album.
		// Involves no SQL query (Gate::check()-filtered, config-driven,
		// in-memory list) — never wrapped in the managed cache.
		/** @var BaseCollection<int,BaseSmartAlbum> $smart_albums */
		$smart_albums = $this->album_factory
			->getAllBuiltInSmartAlbums(false)
			->filter(fn ($smart_album) => Gate::check(AlbumPolicy::CAN_SEE, $smart_album));

		// ── Tag albums ──────────────────────────────────────────────
		$tag_albums_key = $this->cache_key_provider->tagAlbumsListingKey($user_id, $this->sorting);
		/** @var BaseCollection<int,TagAlbum> $tag_albums */
		$tag_albums = $this->managed_cache_service->rememberIf(
			$this->is_cache_albums_enabled,
			$tag_albums_key,
			[$this->cache_key_provider->tagAlbumsListingTag(), $this->cache_key_provider->userTag($user_id), $this->cache_key_provider->albumListingGlobalTag()],
			fn (): BaseCollection => $this->queryTagAlbums($user),
			fn (BaseCollection $albums): array => $this->cache_key_provider->albumTags($albums->map(fn (TagAlbum $a) => $a->id)->all()),
		);

		// ── Person albums ───────────────────────────────────────────
		/** @var BaseCollection<int,PersonAlbum> $person_albums */
		$person_albums = collect();
		if ($this->config_manager->getValueAsBool('ai_vision_face_enabled')) {
			$person_albums_key = $this->cache_key_provider->personAlbumsListingKey($user_id, $this->sorting);
			$person_albums = $this->managed_cache_service->rememberIf(
				$this->is_cache_albums_enabled,
				$person_albums_key,
				[$this->cache_key_provider->personAlbumsListingTag(), $this->cache_key_provider->userTag($user_id), $this->cache_key_provider->albumListingGlobalTag()],
				fn (): BaseCollection => $this->queryPersonAlbums($user),
				fn (BaseCollection $albums): array => $this->cache_key_provider->albumTags($albums->map(fn (PersonAlbum $a) => $a->id)->all()),
			);
		}

		// ── Pinned albums ───────────────────────────────────────────
		$pinned_col = $this->config_manager->getValueAsEnum('sorting_pinned_albums_col', ColumnSortingType::class);
		$pinned_order = $this->config_manager->getValueAsEnum('sorting_pinned_albums_order', OrderSortingType::class);
		$pinned_albums_key = $this->cache_key_provider->pinnedAlbumsListingKey($user_id, $pinned_col, $pinned_order);
		/** @var BaseCollection<int,Album> $pinned_albums */
		$pinned_albums = $this->managed_cache_service->rememberIf(
			$this->is_cache_albums_enabled,
			$pinned_albums_key,
			[$this->cache_key_provider->pinnedAlbumsListingTag(), $this->cache_key_provider->userTag($user_id), $this->cache_key_provider->albumListingGlobalTag()],
			fn (): BaseCollection => $this->queryPinnedAlbums($user, $pinned_col, $pinned_order),
			fn (BaseCollection $albums): array => $this->cache_key_provider->albumTags($albums->map(fn (Album $a) => $a->id)->all()),
		);

		// ── Root / shared albums ────────────────────────────────────
		$root_key = $this->cache_key_provider->rootAlbumsListingKey($user_id, $this->sorting);
		/** @var BaseCollection<int,Album> $albums */
		$albums = $this->managed_cache_service->rememberIf(
			$this->is_cache_albums_enabled,
			$root_key,
			[$this->cache_key_provider->albumChildrenTag(null), $this->cache_key_provider->userTag($user_id), $this->cache_key_provider->albumListingGlobalTag()],
			fn (): BaseCollection => $this->queryRootAlbums($user, $user_id),
			fn (BaseCollection $albums): array => $this->cache_key_provider->albumTags($albums->map(fn (Album $a) => $a->id)->all()),
		);

		if ($user_id !== null) {
			// Ownership partitioning stays in-memory, applied after
			// retrieving the (possibly cached) result — not part of the
			// cached value itself.
			list($a, $b) = $albums->partition(fn ($album) => $album->owner_id === $user_id);

			return new TopAlbumDTO(
				smart_albums: $smart_albums,
				tag_albums: $tag_albums,
				person_albums: $person_albums,
				pinned_albums: $pinned_albums,
				albums: $a->values(),
				shared_albums: $b->values());
		}

		return new TopAlbumDTO(
			smart_albums: $smart_albums,
			tag_albums: $tag_albums,
			person_albums: $person_albums,
			pinned_albums: $pinned_albums,
			albums: $albums);
	}

	private function queryTagAlbums(?User $user): BaseCollection
	{
		$tag_album_query = $this->album_query_policy
			->applyVisibilityFilter(TagAlbum::query()->with(['access_permissions', 'owner', 'userThumbRow.photo.size_variants']), $user);

		return (new SortingDecorator($tag_album_query))
			->orderBy($this->sorting->column, $this->sorting->order)
			->get();
	}

	private function queryPersonAlbums(?User $user): BaseCollection
	{
		$person_album_query = $this->album_query_policy
			->applyVisibilityFilter(PersonAlbum::query()->with(['access_permissions', 'owner', 'userThumbRow.photo.size_variants']), $user);

		return (new SortingDecorator($person_album_query))
			->orderBy($this->sorting->column, $this->sorting->order)
			->get();
	}

	private function queryPinnedAlbums(?User $user, ?ColumnSortingType $pinned_col, ?OrderSortingType $pinned_order): BaseCollection
	{
		$pinned_album_query = $this->album_query_policy
			->applyVisibilityFilter(Album::query()->with(['access_permissions', 'owner'])
			->joinSub(DB::table('base_albums')->select(['id', 'is_pinned'])->where('is_pinned', '=', true), 'pinned', 'pinned.id', '=', 'albums.id'), $user);

		return (new SortingDecorator($pinned_album_query))
			->orderBy($pinned_col, $pinned_order)
			->get();
	}

	private function queryRootAlbums(?User $user, ?int $user_id): BaseCollection
	{
		/** @var AlbumBuilder $query */
		$query = $this->album_query_policy
			->applyVisibilityFilter(Album::query()->with(['access_permissions', 'owner'])->whereIsRoot()
			->when(
				$this->config_manager->getValueAsBool('deduplicate_pinned_albums'),
				fn ($q) => $q
					->joinSub(DB::table('base_albums')->select(['id', 'is_pinned'])->where('is_pinned', '=', false), 'not_pinned', 'not_pinned.id', '=', 'albums.id')
			), $user);

		if ($user_id !== null) {
			// For authenticated users we group albums by ownership.
			return (new SortingDecorator($query))
				->orderBy(ColumnSortingType::OWNER_ID, OrderSortingType::ASC)
				->orderBy($this->sorting->column, $this->sorting->order)
				->get();
		}

		// For anonymous users we don't want to implicitly expose
		// ownership via sorting.
		return (new SortingDecorator($query))
			->orderBy($this->sorting->column, $this->sorting->order)
			->get();
	}
}