<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Policies;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Contracts\Container\BindingResolutionException;

class PhotoPolicy extends BasePolicy
{
	protected AlbumPolicy $albumPolicy;

	public const CAN_SEE = 'canSee';
	public const CAN_DOWNLOAD = 'canDownload';
	public const CAN_DELETE = 'canDelete';
	public const CAN_EDIT = 'canEdit';
	public const CAN_EDIT_ID = 'canEditById';
	public const CAN_ACCESS_FULL_PHOTO = 'canAccessFullPhoto';
	public const CAN_DELETE_BY_ID = 'canDeleteById';

	/**
	 * @throws FrameworkException
	 */
	public function __construct()
	{
		try {
			$this->albumPolicy = resolve(AlbumPolicy::class);
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

		return $photo->album !== null && $this->albumPolicy->canAccess($user, $photo->album);
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

		return $this->canSee($user, $photo) && $this->albumPolicy->canDownload($user, $photo->album);
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

		return $this->canSee($user, $photo) && $this->albumPolicy->canEdit($user, $photo->album);
	}

	/**
	 * Checks whether the designated photos are editable by the current user.
	 *
	 * @param User     $user
	 * @param string[] $photoIDs
	 *
	 * @return bool
	 *
	 * @throws QueryBuilderException
	 */
	public function canEditById(User $user, array $photoIDs): bool
	{
		// Make IDs unique as otherwise count will fail.
		$photoIDs = array_unique($photoIDs);

		if (
			$user->may_upload &&
			Photo::query()
			->whereIn('id', $photoIDs)
			->where('owner_id', $user->id)
			->count() === count($photoIDs)
		) {
			return true;
		}

		$parents_id = Photo::query()
			->select('album_id')
			->whereIn('id', $photoIDs)
			->groupBy('album_id')
			->pluck('album_id')->all();

		return $this->albumPolicy->canEditById($user, $parents_id);
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

		return $this->albumPolicy->canAccessFullPhoto($user, $photo->album);
	}

	/**
	 * Checks whether the photo is deletable le by the current user.
	 *
	 * @param Photo $photo
	 *
	 * @return bool
	 */
	public function canDelete(User $user, Photo $photo)
	{
		if ($this->isOwner($user, $photo)) {
			return true;
		}

		return $this->canSee($user, $photo) && $this->albumPolicy->canDelete($user, $photo->album);
	}

	/**
	 * Checks whether the designated photos are deletable by the current user.
	 *
	 * @param User     $user
	 * @param string[] $photoIDs
	 *
	 * @return bool
	 *
	 * @throws QueryBuilderException
	 */
	public function canDeleteById(User $user, array $photoIDs): bool
	{
		// Make IDs unique as otherwise count will fail.
		$photoIDs = array_unique($photoIDs);

		if (
			$user->may_upload &&
			Photo::query()
			->whereIn('id', $photoIDs)
			->where('owner_id', $user->id)
			->count() === count($photoIDs)
		) {
			return true;
		}

		// If there are any photos which are not in albums at this point, we fail.
		if (Photo::query()
			->whereNull('album_id')
			->whereIn('id', $photoIDs)
			->count() > 0
		) {
			return false;
		}

		$parentIDs = Photo::query()
			->select('album_id')
			->whereIn('id', $photoIDs)
			->groupBy('album_id')
			->pluck('album_id')->all();

		return $this->albumPolicy->canDeleteById($user, $parentIDs);
	}
}
