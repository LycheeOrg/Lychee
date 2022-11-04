<?php

namespace App\Policies;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Contracts\Container\BindingResolutionException;

class PhotoPolicy extends BasePolicy
{
	protected AlbumPolicy $albumPolicy;

	public const CAN_SEE = 'canSee';
	public const CAN_DOWNLOAD = 'canDownload';
	public const CAN_EDIT = 'canEdit';
	public const CAN_EDIT_ID = 'canEditById';
	public const CAN_ACCESS_FULL_PHOTO = 'canAccessFullPhoto';

	/**
	 * @throws FrameworkException
	 */
	public function __construct()
	{
		try {
			$this->albumPolicy = resolve(AlbumPolicy::class);
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s provider component', $e);
		}
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
		return $this->isOwner($user, $photo) ||
			$photo->is_public ||
			(
				$photo->album !== null &&
				$this->albumPolicy->canAccess($user, $photo->album)
			);
	}

	/**
	 * Checks whether the photo may be downloaded by the current user.
	 *
	 * Previously, this code was part of {@link Archive::extractFileInfo()}.
	 * In particular, the method threw to {@link UnauthorizedException} with
	 * custom error messages:
	 *
	 *  - `'User is not allowed to download the image'`, if the user was not
	 *    the owner, the user was allowed to see the photo (i.e. the album
	 *    is shared with the user), but the album does not allow to download
	 *    photos
	 *  - `'Permission to download is disabled by configuration'`, if the
	 *    user was not the owner, the photo was not part of any album (i.e.
	 *    unsorted), the photo was public and downloading was disabled by
	 *    configuration.
	 *
	 * TODO: Check if these custom error messages are still needed. If yes, consider not to return a boolean value but rename the method to `assert...` and throw exceptions with custom error messages.
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

		if (!$this->canSee($user, $photo)) {
			return false;
		}

		return $this->albumPolicy->canDownload($user, $photo->album);
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
		return $this->isOwner($user, $photo);
	}

	/**
	 * Checks whether the designated photos are editable by the current user.
	 *
	 * See {@link PhotoQueryPolicy::isEditable()} for the definition
	 * when a photo is editable.
	 *
	 * This method is mostly only useful during deletion of photos, when no
	 * photo models are loaded for efficiency reasons.
	 * If a photo model is required anyway (because it shall be edited),
	 * then first load the photo once and use
	 * {@link PhotoQueryPolicy::isEditable()}
	 * instead in order to avoid several DB requests.
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
		if (!$this->albumPolicy->canUpload($user, null)) {
			return false;
		}

		// Make IDs unique as otherwise count will fail.
		$photoIDs = array_unique($photoIDs);

		return
			count($photoIDs) === 0 ||
			Photo::query()
				->whereIn('id', $photoIDs)
				->where('owner_id', $user->id)
				->count() === count($photoIDs);
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

		return ($photo->album !== null && $photo->album->grants_full_photo_access) ||
			($photo->album === null && Configs::getValueAsBool('full_photo'));
	}
}
