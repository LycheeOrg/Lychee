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
use App\Services\Cache\ManagedCacheService;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class Top
{
	private AlbumSortingCriterion $sorting;

	/**
	 * @throws InvalidOrderDirectionException
	 * @throws ConfigurationKeyMissingException
	 */
	public function __construct(
		private AlbumFactory $album_factory,
		private AlbumQueryPolicy $album_query_policy,
		protected readonly ConfigManager $config_manager,
		protected readonly ManagedCacheService $managed_cache_service,
	) {
		$this->sorting = AlbumSortingCriterion::createDefault();
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
		$user_key = $user_id ?? 'guest';
		$managed_cache_albums_enabled = $this->config_manager->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $this->config_manager->getValueAsInt('managed_cache_ttl');

		// Do not eagerly load the relation `photos` for each smart album.
		// On the albums overview, we only need a thumbnail for each album.
		// Involves no SQL query (Gate::check()-filtered, config-driven,
		// in-memory list) — never wrapped in the managed cache.
		/** @var BaseCollection<int,BaseSmartAlbum> $smart_albums */
		$smart_albums = $this->album_factory
			->getAllBuiltInSmartAlbums(false)
			->filter(fn ($smart_album) => Gate::check(AlbumPolicy::CAN_SEE, $smart_album));

		// ── Tag albums ──────────────────────────────────────────────
		$tag_albums_key = "tag-albums-listing:user:{$user_key}:sort:{$this->sorting->column->value}:{$this->sorting->order->value}";
		/** @var BaseCollection<int,TagAlbum> $tag_albums */
		$tag_albums = $this->managed_cache_service->rememberIf(
			$managed_cache_albums_enabled,
			$tag_albums_key,
			['tag-albums-listing', "user:{$user_key}", 'album-listing-global'],
			$ttl,
			function () use ($user): BaseCollection {
				$tag_album_query = $this->album_query_policy
					->applyVisibilityFilter(TagAlbum::query()->with(['access_permissions', 'owner', 'userThumbRow.photo.size_variants']), $user);

				return (new SortingDecorator($tag_album_query))
					->orderBy($this->sorting->column, $this->sorting->order)
					->get();
			}
		);
		$this->managed_cache_service->addTags($tag_albums_key, $tag_albums->map(fn (TagAlbum $a) => 'album:' . $a->id)->all());

		// ── Person albums ───────────────────────────────────────────
		/** @var BaseCollection<int,PersonAlbum> $person_albums */
		$person_albums = collect();
		if ($this->config_manager->getValueAsBool('ai_vision_face_enabled')) {
			$person_albums_key = "person-albums-listing:user:{$user_key}:sort:{$this->sorting->column->value}:{$this->sorting->order->value}";
			$person_albums = $this->managed_cache_service->rememberIf(
				$managed_cache_albums_enabled,
				$person_albums_key,
				['person-albums-listing', "user:{$user_key}", 'album-listing-global'],
				$ttl,
				function () use ($user): BaseCollection {
					$person_album_query = $this->album_query_policy
						->applyVisibilityFilter(PersonAlbum::query()->with(['access_permissions', 'owner', 'userThumbRow.photo.size_variants']), $user);

					return (new SortingDecorator($person_album_query))
						->orderBy($this->sorting->column, $this->sorting->order)
						->get();
				}
			);
			$this->managed_cache_service->addTags($person_albums_key, $person_albums->map(fn (PersonAlbum $a) => 'album:' . $a->id)->all());
		}

		// ── Pinned albums ───────────────────────────────────────────
		$pinned_col = $this->config_manager->getValueAsEnum('sorting_pinned_albums_col', ColumnSortingType::class);
		$pinned_order = $this->config_manager->getValueAsEnum('sorting_pinned_albums_order', OrderSortingType::class);
		$pinned_albums_key = 'pinned-albums-listing:user:' . $user_key . ':sort:' . ($pinned_col?->value ?? 'null') . ':' . ($pinned_order?->value ?? 'null');
		/** @var BaseCollection<int,Album> $pinned_albums */
		$pinned_albums = $this->managed_cache_service->rememberIf(
			$managed_cache_albums_enabled,
			$pinned_albums_key,
			['pinned-albums-listing', "user:{$user_key}", 'album-listing-global'],
			$ttl,
			function () use ($user, $pinned_col, $pinned_order): BaseCollection {
				$pinned_album_query = $this->album_query_policy
					->applyVisibilityFilter(Album::query()->with(['access_permissions', 'owner'])
					->joinSub(DB::table('base_albums')->select(['id', 'is_pinned'])->where('is_pinned', '=', true), 'pinned', 'pinned.id', '=', 'albums.id'), $user);

				return (new SortingDecorator($pinned_album_query))
					->orderBy($pinned_col, $pinned_order)
					->get();
			}
		);
		$this->managed_cache_service->addTags($pinned_albums_key, $pinned_albums->map(fn (Album $a) => 'album:' . $a->id)->all());

		// ── Root / shared albums ────────────────────────────────────
		$root_key = "album-children:root:user:{$user_key}:sort:{$this->sorting->column->value}:{$this->sorting->order->value}";
		/** @var BaseCollection<int,Album> $albums */
		$albums = $this->managed_cache_service->rememberIf(
			$managed_cache_albums_enabled,
			$root_key,
			['album-children:root', "user:{$user_key}", 'album-listing-global'],
			$ttl,
			function () use ($user, $user_id): BaseCollection {
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
		);
		$this->managed_cache_service->addTags($root_key, $albums->map(fn (Album $a) => 'album:' . $a->id)->all());

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
}