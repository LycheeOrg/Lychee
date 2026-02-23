<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\SmartAlbums;

use App\Constants\PhotoAlbum as PA;
use App\Contracts\Exceptions\InternalLycheeException;
use App\Contracts\Models\AbstractAlbum;
use App\DTO\PhotoSortingCriterion;
use App\Enum\SmartAlbumType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\InvalidOrderDirectionException;
use App\Exceptions\Internal\InvalidQueryModelException;
use App\Exceptions\InvalidPropertyException;
use App\Models\AccessPermission;
use App\Models\Extensions\SortingDecorator;
use App\Models\Extensions\Thumb;
use App\Models\Extensions\ToArrayThrowsNotImplemented;
use App\Models\Extensions\UTCBasedTimes;
use App\Models\Photo;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoQueryPolicy;
use App\Repositories\ConfigManager;
use App\SmartAlbums\Utils\MimicModel;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

/**
 * Class BaseSmartAlbum.
 *
 * The common base class for all built-in smart albums which can neither
 * be created nor deleted, but always exist.
 * Smart albums are never explicit "parent albums" of photos.
 * Photos belong to these albums due to certain properties like being
 * highlighted, being recently added, etc.
 */
abstract class BaseSmartAlbum implements AbstractAlbum
{
	use MimicModel;
	use UTCBasedTimes;
	use ToArrayThrowsNotImplemented;

	protected PhotoQueryPolicy $photo_query_policy;
	protected string $id;
	protected string $title;
	protected ?Thumb $thumb = null;
	/** @var ?LengthAwarePaginator<int,Photo> */
	protected ?LengthAwarePaginator $photos = null;
	protected \Closure $smart_photo_condition;
	protected AccessPermission|null $public_permissions;
	protected ConfigManager $config_manager;

	/**
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 */
	protected function __construct(
		SmartAlbumType $id,
		\Closure $smart_condition,
	) {
		try {
			$this->config_manager = resolve(ConfigManager::class);
			$this->photo_query_policy = resolve(PhotoQueryPolicy::class);
			$this->id = $id->value;
			$this->title = __('gallery.smart_album.' . strtolower($id->name)) ?? $id->name;
			$this->smart_photo_condition = $smart_condition;
			/** @var AccessPermission|null $perm */
			$perm = AccessPermission::query()->where('base_album_id', '=', $id->value)->first();
			$this->public_permissions = $perm;
			// @codeCoverageIgnoreStart
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s service container', $e);
		}
		// @codeCoverageIgnoreEnd
	}

	// From AbstractAlbum
	public function get_id(): string
	{
		return $this->id;
	}

	public function get_title(): string
	{
		return $this->title;
	}

	/**
	 * @return LengthAwarePaginator<int,Photo>
	 */
	public function get_photos(): LengthAwarePaginator
	{
		return $this->getPhotosAttribute();
	}

	/**
	 * @return \App\Eloquent\FixedQueryBuilder<Photo>
	 *
	 * @throws InternalLycheeException
	 */
	public function photos(): Builder
	{
		/** @var ?User $user */
		$user = Auth::user();
		$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();

		$base_query = Photo::query()->leftJoin(PA::PHOTO_ALBUM, 'photos.id', '=', PA::PHOTO_ID)->with(['size_variants', 'statistics', 'palette', 'tags', 'rating']);

		if (!$this->config_manager->getValueAsBool('SA_override_visibility')) {
			return $this->photo_query_policy
				->applySearchabilityFilter(query: $base_query, user: $user, unlocked_album_ids: $unlocked_album_ids, origin: null, include_nsfw: !$this->config_manager->getValueAsBool('hide_nsfw_in_smart_albums'))
				->when(
					$this->config_manager->getValueAsBool('enable_smart_album_per_owner') && Auth::check(),
					fn (Builder $query) => $query->where('photos.owner_id', '=', Auth::id())
				)
				->where($this->smart_photo_condition);
		}

		// If the smart album visibility override is enabled, we do not need to apply any security filter, as all photos are visible
		// in this smart album. We still need to apply the smart album condition, though.
		return $this->photo_query_policy->applySensitivityFilter(query: $base_query, user: $user, origin: null, include_nsfw: !$this->config_manager->getValueAsBool('hide_nsfw_in_smart_albums'))
			->where($this->smart_photo_condition);
	}

	/**
	 * @return LengthAwarePaginator<int,Photo>
	 *
	 * @throws InvalidOrderDirectionException
	 * @throws InvalidQueryModelException
	 */
	protected function getPhotosAttribute(): LengthAwarePaginator
	{
		// Cache query result for later use
		// (this mimics the behaviour of relations of true Eloquent models)
		if ($this->photos === null) {
			$sorting = PhotoSortingCriterion::createDefault();

			/** @var \Illuminate\Pagination\LengthAwarePaginator<int,\App\Models\Photo> $photos */
			$photos = (new SortingDecorator($this->photos()))
				->orderPhotosBy($sorting->column, $sorting->order)
				->paginate($this->config_manager->getValueAsInt('photos_pagination_limit'));
			$this->photos = $photos;
		}

		return $this->photos;
	}

	/**
	 * Similar to the function above.
	 * The big difference is that we do not check if it is null or not.
	 *
	 * @return LengthAwarePaginator<int,Photo>|null
	 */
	public function getPhotos(): ?LengthAwarePaginator
	{
		return $this->photos;
	}

	/**
	 * @throws InvalidPropertyException
	 * @throws InvalidQueryModelException
	 */
	public function get_thumb(): Thumb|null
	{
		return $this->getThumbAttribute();
	}

	/**
	 * @throws InvalidPropertyException
	 * @throws InvalidQueryModelException
	 */
	protected function getThumbAttribute(): ?Thumb
	{
		/*
			* Note, `photos()` already applies a "security filter" and
			* only returns photos which are accessible by the current
			* user.
			*/
		$this->thumb ??= $this->config_manager->getValueAsBool('SA_random_thumbs')
		// @codeCoverageIgnoreStart
		? Thumb::createFromRandomQueryable($this->photos())
		// @codeCoverageIgnoreEnd
		: $this->thumb = Thumb::createFromQueryable(
			$this->photos(),
			PhotoSortingCriterion::createDefault()
		);

		return $this->thumb;
	}

	public function public_permissions(): ?AccessPermission
	{
		return $this->public_permissions;
	}

	public function setPublic(): void
	{
		if ($this->public_permissions !== null) {
			return;
		}

		$this->public_permissions = AccessPermission::ofPublic();
		$this->public_permissions->base_album_id = $this->id;
		$this->public_permissions->save();
	}

	public function setPublicHidden(): void
	{
		if ($this->public_permissions !== null) {
			return;
		}

		$this->public_permissions = AccessPermission::ofPublicHidden();
		$this->public_permissions->base_album_id = $this->id;
		$this->public_permissions->save();
	}

	public function setPrivate(): void
	{
		if ($this->public_permissions === null) {
			return;
		}

		$perm = $this->public_permissions;
		$this->public_permissions = null;
		$perm->delete();
	}
}
