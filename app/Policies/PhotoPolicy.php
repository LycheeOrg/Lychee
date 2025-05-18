<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Policies;

use App\Constants\PhotoAlbum as PA;
use App\Enum\MetricsAccess;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\UnauthorizedException;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PhotoPolicy extends BasePolicy
{
	protected AlbumPolicy $album_policy;

	public const CAN_SEE = 'canSee';
	public const CAN_DOWNLOAD = 'canDownload';
	// public const CAN_DELETE = 'canDelete';
	public const CAN_EDIT = 'canEdit';
	public const CAN_EDIT_ID = 'canEditById';
	public const CAN_ACCESS_FULL_PHOTO = 'canAccessFullPhoto';
	public const CAN_DELETE_BY_ID = 'canDeleteById';
	public const CAN_READ_METRICS = 'canReadMetrics';

	/**
	 * @throws FrameworkException
	 */
	public function __construct()
	{
		try {
			$this->album_policy = resolve(AlbumPolicy::class);
			// @codeCoverageIgnoreStart
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s provider component', $e);
		}
		// @codeCoverageIgnoreEnd
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
	 * Checks whether the photo is deletable le by the current user.
	 *
	 * @param Photo $photo
	 *
	 * @return bool
	 */
	// public function canDelete(User $user, Photo $photo)
	// {
	// 	if ($this->isOwner($user, $photo)) {
	// 		return true;
	// 	}

	// 	// TODO: refactor me.
	// 	throw new UnauthorizedException('You are not allowed to delete this photo (yet).');

	// 	return $this->canSee($user, $photo) && $this->album_policy->canDelete($user, $photo->album);
	// }

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
		$access_level = Configs::getValueAsEnum('metrics_access', MetricsAccess::class);

		return match ($access_level) {
			MetricsAccess::PUBLIC => true,
			MetricsAccess::LOGGED_IN => $user !== null,
			MetricsAccess::OWNER => $user !== null && $photo->owner_id === $user->id,
			MetricsAccess::ADMIN => $user?->may_administrate === true,
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
