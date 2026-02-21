<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Policies;

use App\Constants\PhotoAlbum as PA;
use App\Enum\MetricsAccess;
use App\Enum\PhotoHighlightVisibilityType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use App\Repositories\ConfigManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PhotoPolicy extends BasePolicy
{
	public const CAN_SEE = 'canSee';
	public const CAN_DOWNLOAD = 'canDownload';
	public const CAN_EDIT = 'canEdit';
	public const CAN_EDIT_ID = 'canEditById';
	public const CAN_ACCESS_FULL_PHOTO = 'canAccessFullPhoto';
	public const CAN_DELETE_BY_ID = 'canDeleteById';
	public const CAN_READ_METRICS = 'canReadMetrics';
	public const CAN_READ_RATINGS = 'canReadRatings';
	public const CAN_STAR = 'canStar';

	/**
	 * @throws FrameworkException
	 */
	public function __construct(
		protected AlbumPolicy $album_policy,
	) {
	}

	/**
	 * This ensures that current photo is owned by current user.
	 *
	 * @param User|null $user
	 * @param Photo     $photo
	 *
	 * @return bool
	 */
	private function isOwner(?User $user, Photo $photo): bool
	{
		return $user !== null && $photo->owner_id === $user->id;
	}

	private function hasAlbums(Photo $photo): bool
	{
		return $photo->albums !== null && !$photo->albums->isEmpty();
	}

	/**
	 * Defines whether the photo is visible to the current user.
	 *
	 * @param User|null $user
	 * @param Photo     $photo
	 *
	 * @return bool
	 */
	public function canSee(?User $user, Photo $photo): bool
	{
		if ($this->isOwner($user, $photo)) {
			return true;
		}

		return $this->hasAlbums($photo) && $this->reduction($photo->albums, fn ($a) => $this->album_policy->canAccess($user, $a));
	}

	/**
	 * Checks whether the photo may be downloaded by the current user.
	 *
	 * @param User|null $user
	 * @param Photo     $photo
	 *
	 * @return bool
	 */
	public function canDownload(?User $user, Photo $photo): bool
	{
		if ($this->isOwner($user, $photo)) {
			return true;
		}

		return $this->hasAlbums($photo) && $this->reduction($photo->albums, fn ($a) => $this->album_policy->canDownload($user, $a));
	}

	/**
	 * Checks whether the photo is editable by the current user.
	 *
	 * A photo is called _editable_ if the current user is allowed to edit
	 * the photo's properties.
	 * A photo is _editable_ if any of the following conditions hold
	 * (OR-clause)
	 *
	 *  - the user is an admin
	 *  - the user is the owner of the photo
	 *
	 * @param Photo $photo
	 *
	 * @return bool
	 */
	public function canEdit(User $user, Photo $photo)
	{
		if ($this->isOwner($user, $photo)) {
			return true;
		}

		return $this->hasAlbums($photo) && $this->reduction($photo->albums, fn ($a) => $this->album_policy->canEdit($user, $a));
	}

	/**
	 * Checks whether the designated photos are editable by the current user.
	 *
	 * @param User     $user
	 * @param string[] $photo_ids
	 *
	 * @return bool
	 *
	 * @throws QueryBuilderException
	 */
	public function canEditById(User $user, array $photo_ids): bool
	{
		// Make IDs unique as otherwise count will fail.
		$photo_ids = array_unique($photo_ids);

		if (
			$user->may_upload &&
			Photo::query()
			->whereIn('id', $photo_ids)
			->where('owner_id', $user->id)
			->count() === count($photo_ids)
		) {
			return true;
		}

		$parents_id = DB::table(PA::PHOTO_ALBUM)
			->select(PA::ALBUM_ID)
			->whereIn(PA::PHOTO_ID, $photo_ids)
			->groupBy('album_id')
			->pluck('album_id')->all();

		return $this->album_policy->canEditById($user, $parents_id);
	}

	/**
	 * Checks whether the photo may be seen full resolution by the current user.
	 *
	 * @param User|null $user
	 * @param Photo     $photo
	 *
	 * @return bool
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function canAccessFullPhoto(?User $user, Photo $photo): bool
	{
		if ($this->isOwner($user, $photo)) {
			return true;
		}

		if (!$this->canSee($user, $photo)) {
			return false;
		}

		return $this->hasAlbums($photo) && $this->reduction($photo->albums, fn ($a) => $this->album_policy->canAccessFullPhoto($user, $a));
	}

	/**
	 * Checks whether the designated photos are deletable by the current user.
	 *
	 * @param User     $user
	 * @param string[] $photo_ids
	 *
	 * @return bool
	 *
	 * @throws QueryBuilderException
	 */
	public function canDeleteById(User $user, array $photo_ids): bool
	{
		// Make IDs unique as otherwise count will fail.
		$photo_ids = array_unique($photo_ids);

		if (
			$user->may_upload &&
			Photo::query()
			->whereIn('id', $photo_ids)
			->where('owner_id', $user->id)
			->count() === count($photo_ids)
		) {
			return true;
		}

		// If there are any photos which are not in albums at this point, we fail.
		if (
			Photo::query()
			->leftJoin(PA::PHOTO_ALBUM, 'photos.id', '=', PA::PHOTO_ID)
			->whereNull('album_id')
			->whereIn('photos.id', $photo_ids)
			->count() > 0
		) {
			return false;
		}

		$parent_ids = DB::table(PA::PHOTO_ALBUM)
			->select(PA::ALBUM_ID)
			->whereIn(PA::PHOTO_ID, $photo_ids)
			->groupBy(PA::ALBUM_ID)
			->pluck('album_id')->all();

		return $this->album_policy->canDeleteById($user, $parent_ids);
	}

	/**
	 * Check whether the user can read the metrics of the photo.
	 *
	 * @param User|null $user
	 * @param Photo     $photo
	 *
	 * @return bool
	 */
	public function canReadMetrics(?User $user, Photo $photo): bool
	{
		$config_manager = app(ConfigManager::class);
		$access_level = $config_manager->getValueAsEnum('metrics_access', MetricsAccess::class);

		return match ($access_level) {
			MetricsAccess::PUBLIC => true,
			MetricsAccess::LOGGED_IN => $user !== null,
			MetricsAccess::OWNER => $user !== null && $photo->owner_id === $user->id,
			MetricsAccess::ADMIN => $user?->may_administrate === true,
			default => false,
		};
	}

	/**
	 * @param User|null $user
	 * @param Photo     $photo
	 *
	 * @return bool
	 */
	public function canReadRatings(?User $user, Photo $photo): bool
	{
		$config_manager = app(ConfigManager::class);
		// Rating are disabled globally
		if (!$config_manager->getValueAsBool('rating_enabled')) {
			return false;
		}

		// Note that this will bypass the setting 'rating_show_only_when_user_rated'
		// It is up to the admin to decide whether anonymous users can see ratings at all.
		return ($user !== null) || $config_manager->getValueAsBool('rating_public');
	}

	/**
	 * Checks whether the photo can be starred by the current user.
	 *
	 * A photo is called _starred_ if the current user is allowed to star
	 * the photo.
	 * A photo can be _starred_ if any of the following conditions hold
	 * (OR-clause)
	 *
	 * - the settings is set to allow anonymous users to star photos
	 * - the settings is set to allow authenticated users to star photos and the user is authenticated
	 * - the settings is set to allow editors to star photos and the user is an editor
	 * - the user is the owner of the photo (checked via canEdit method)
	 * - the user is admin (checked by before method)
	 *
	 * @param User|null $user
	 * @param Photo     $photo
	 *
	 * @return bool
	 */
	public function canStar(?User $user, Photo $photo): bool
	{
		$config_manager = app(ConfigManager::class);
		$visibility = $config_manager->getValueAsEnum('photos_star_visibility', PhotoHighlightVisibilityType::class);

		return match ($visibility) {
			PhotoHighlightVisibilityType::ANONYMOUS => true,
			PhotoHighlightVisibilityType::AUTHENTICATED => $user !== null,
			PhotoHighlightVisibilityType::EDITOR => $user !== null && $this->canEdit($user, $photo),
			default => false,
		};
	}

	/**
	 * @param Collection<int,Album>        $albums
	 * @param \Closure(Album $album): bool $reducer
	 *
	 * @return bool
	 */
	private function reduction(Collection $albums, \Closure $reducer): bool
	{
		return $albums->reduce(
			fn (bool $carry, Album $album) => $carry || $reducer($album),
			false
		);
	}
}
