<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Policies;

use App\Constants\AccessPermissionConstants as APC;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\SmartAlbumType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\BaseAlbumImpl;
use App\Models\Configs;
use App\Models\Extensions\BaseAlbum;
use App\Models\User;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Support\Facades\Session;

class AlbumPolicy extends BasePolicy
{
	public const UNLOCKED_ALBUMS_SESSION_KEY = 'unlocked_albums';

	public const IS_OWNER = 'isOwner';
	public const CAN_SEE = 'canSee';
	public const CAN_ACCESS = 'canAccess';
	public const CAN_ACCESS_FULL_PHOTO = 'canAccessFullPhoto';
	public const CAN_ACCESS_MAP = 'canAccessMap';
	public const CAN_DOWNLOAD = 'canDownload';
	public const CAN_DELETE = 'canDelete';
	public const CAN_TRANSFER = 'canTransfer';
	public const CAN_UPLOAD = 'canUpload';
	public const CAN_EDIT = 'canEdit';
	public const CAN_EDIT_ID = 'canEditById';
	public const CAN_DELETE_ID = 'canDeleteById';
	public const CAN_SHARE = 'canShare';
	public const CAN_SHARE_WITH_USERS = 'canShareWithUsers';
	public const CAN_IMPORT_FROM_SERVER = 'canImportFromServer';
	public const CAN_SHARE_ID = 'canShareById';

	/**
	 * This ensures that current album is owned by current user.
	 *
	 * @param User|null               $user
	 * @param BaseAlbum|BaseAlbumImpl $album
	 *
	 * @return bool
	 */
	public function isOwner(?User $user, BaseAlbum|BaseAlbumImpl $album): bool
	{
		return $user !== null && $album->owner_id === $user->id;
	}

	/**
	 * Checks whether the currentuser can see said album.
	 *
	 * Note, at the moment this check is only needed for built-in smart
	 * albums.
	 * Hence, the method is only provided for them.
	 *
	 * @param User|null      $user
	 * @param BaseSmartAlbum $smartAlbum
	 *
	 * @return bool true, if the album is visible
	 */
	public function canSee(?User $user, BaseSmartAlbum $smartAlbum): bool
	{
		return ($user?->may_upload === true) ||
			$smartAlbum->public_permissions() !== null;
	}

	/**
	 * Checks whether current user can access the album.
	 *
	 * A real albums (i.e. albums that are stored in the DB) is called
	 * _accessible_ if the current user is allowed to browse into it, i.e. if
	 * the current user may open it and see its content.
	 * An album is _accessible_ if any of the following conditions hold
	 * (OR-clause)
	 *
	 *  - the user is an admin
	 *  - the user is the owner of the album
	 *  - the album is shared with the user
	 *  - the album is public AND no password is set
	 *  - the album is public AND has been unlocked
	 *
	 * In other cases, the following holds:
	 *  - the root album is accessible by everybody
	 *  - the built-in smart albums are accessible, if
	 *     - the user is authenticated and is granted the right of uploading, or
	 *     - the album is public
	 *
	 * @param User|null          $user
	 * @param AbstractAlbum|null $album
	 *
	 * @return bool
	 */
	public function canAccess(?User $user, ?AbstractAlbum $album): bool
	{
		if ($album === null) {
			return true;
		}

		if (!$album instanceof BaseAlbum) {
			/** @var BaseSmartAlbum $album */
			return $this->canSee($user, $album);
		}

		if ($this->isOwner($user, $album)) {
			return true;
		}

		if ($album->current_user_permissions() !== null) {
			return true;
		}

		if (
			$album->public_permissions() !== null &&
			($album->public_permissions()->password === null ||
				$this->isUnlocked($album))
		) {
			return true;
		}

		return false;
	}

	/**
	 * Check if user can access the map.
	 * Note that this is not used to determine the visibility of the header button because
	 * 1. Admin will always return true.
	 * 2. We also check if there are pictures with location data to be display in the album.
	 *
	 * @param User|null          $user
	 * @param AbstractAlbum|null $album
	 *
	 * @return bool
	 */
	public function canAccessMap(?User $user, ?AbstractAlbum $album): bool
	{
		if (!Configs::getValueAsBool('map_display')) {
			return false;
		}

		if ($user === null && !Configs::getValueAsBool('map_display_public')) {
			return false;
		}

		return $this->canAccess($user, $album);
	}

	/**
	 * Check if current user can download the album.
	 *
	 * @param User|null          $user
	 * @param AbstractAlbum|null $abstractAlbum
	 *
	 * @return bool
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function canDownload(?User $user, ?AbstractAlbum $abstractAlbum): bool
	{
		// The root album always uses the global setting
		if ($abstractAlbum === null) {
			return Configs::getValueAsBool('grants_download');
		}

		// User is logged in
		// Or User can download.
		if (!$abstractAlbum instanceof BaseAlbum) {
			return $user !== null || $abstractAlbum->public_permissions()?->grants_download === true;
		}

		return $this->isOwner($user, $abstractAlbum) ||
			$abstractAlbum->current_user_permissions()?->grants_download === true ||
			$abstractAlbum->public_permissions()?->grants_download === true;
	}

	/**
	 * Check if user is allowed to upload in current albumn.
	 *
	 * @param User               $user
	 * @param AbstractAlbum|null $abstractAlbum
	 *
	 * @return bool
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function canUpload(User $user, ?AbstractAlbum $abstractAlbum = null): bool
	{
		// The upload right on the root album is directly determined by the user's capabilities.
		if ($abstractAlbum === null || !$abstractAlbum instanceof BaseAlbum) {
			return $user->may_upload;
		}

		return $this->isOwner($user, $abstractAlbum) ||
			$abstractAlbum->current_user_permissions()?->grants_upload === true ||
			$abstractAlbum->public_permissions()?->grants_upload === true;
	}

	/**
	 * Checks whether the album is editable by the current user.
	 *
	 * An album is called _editable_ if the current user is allowed to edit
	 * the album's properties.
	 * This also covers adding new photos to an album.
	 * An album is _editable_ if any of the following conditions hold
	 * (OR-clause)
	 *
	 *  - the user is an admin
	 *  - the user has the upload privilege and is the owner of the album
	 *
	 * Note about built-in smart albums:
	 * The built-in smart albums (starred, public, recent, unsorted) do not
	 * have any editable properties.
	 * Hence, it is pointless whether a smart album is editable or not.
	 * In order to silently ignore/skip this condition for smart albums,
	 * this method always returns `true` for a smart album.
	 *
	 * @param User               $user
	 * @param AbstractAlbum|null $album the album; `null` designates the root album
	 *
	 * @return bool
	 */
	public function canEdit(User $user, AbstractAlbum|null $album): bool
	{
		// The root album and smart albums get a pass
		if ($album === null || $album instanceof BaseSmartAlbum) {
			return $user->may_upload;
		}

		if ($album instanceof BaseAlbum) {
			return ($this->isOwner($user, $album) && $user->may_upload) ||
				$album->current_user_permissions()?->grants_edit === true ||
				$album->public_permissions()?->grants_edit === true;
		}

		return false;
	}

	/**
	 * Check if user is allowed to USE delete in current albumn.
	 *
	 * @param User               $user
	 * @param AbstractAlbum|null $abstractAlbum
	 *
	 * @return bool
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function canDelete(User $user, ?AbstractAlbum $abstractAlbum = null): bool
	{
		if ($abstractAlbum instanceof BaseSmartAlbum) {
			return $user->may_upload;
		}

		if (!$abstractAlbum instanceof Album) {
			return $user->may_upload;
		}

		if ($this->isOwner($user, $abstractAlbum)) {
			return true;
		}

		/** @var Album $abstractAlbum */
		if (
			AccessPermission::query()
			->where(APC::BASE_ALBUM_ID, '=', $abstractAlbum->parent_id)
			->where(APC::USER_ID, '=', $user->id)
			->where(APC::GRANTS_DELETE, '=', true)
			->count() === 1
		) {
			return true;
		}

		return false;
	}

	/**
	 * Check if user is allowed to USE transfer in current album.
	 *
	 * @param User               $user
	 * @param AbstractAlbum|null $baseAlbum
	 *
	 * @return bool
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function canTransfer(User $user, ?AbstractAlbum $baseAlbum = null): bool
	{
		if (!$baseAlbum instanceof BaseAlbum) {
			return false;
		}

		return $this->isOwner($user, $baseAlbum);
	}

	/**
	 * Checks whether the album-user has the full photo access.
	 *
	 * @param User|null          $user
	 * @param AbstractAlbum|null $abstractAlbum
	 *
	 * @return bool
	 */
	public function canAccessFullPhoto(?User $user, ?AbstractAlbum $abstractAlbum): bool
	{
		if ($abstractAlbum === null || $abstractAlbum instanceof BaseSmartAlbum) {
			return Configs::getValueAsBool('grants_full_photo_access');
		}

		/** @var BaseAlbum $abstractAlbum */
		if ($this->isOwner($user, $abstractAlbum)) {
			return true;
		}

		return $abstractAlbum->public_permissions()?->grants_full_photo_access === true ||
			$abstractAlbum->current_user_permissions()?->grants_full_photo_access === true;
	}

	/**
	 * Checks whether the designated albums are editable by the current user.
	 *
	 * See {@link AlbumQueryPolicy::isEditable()} for the definition
	 * when an album is editable.
	 *
	 * This method is mostly only useful during deletion of albums, when no
	 * album models are loaded for efficiency reasons.
	 * If an album model is required anyway (because it shall be edited),
	 * then first load the album once and use
	 * {@link AlbumQueryPolicy::isEditable()}
	 * instead in order to avoid several DB requests.
	 *
	 * @param User              $user
	 * @param array<int,string> $albumIDs
	 *
	 * @return bool
	 *
	 * @throws QueryBuilderException
	 */
	public function canEditById(User $user, array $albumIDs): bool
	{
		$albumIDs = $this->uniquify($albumIDs);
		$num_albums = count($albumIDs);

		if ($num_albums === 0) {
			return $user->may_upload;
		}

		if (
			BaseAlbumImpl::query()
			->whereIn('id', $albumIDs)
			->where('owner_id', '=', $user->id)
			->count() === $num_albums
		) {
			return $user->may_upload;
		}

		if (
			AccessPermission::query()
			->whereIn(APC::BASE_ALBUM_ID, $albumIDs)
			->where(APC::USER_ID, '=', $user->id)
			->where(APC::GRANTS_EDIT, '=', true)
			->count() === $num_albums
		) {
			return true;
		}

		return false;
	}

	/**
	 * Checks whether the designated albums are editable by the current user.
	 *
	 * See {@link AlbumQueryPolicy::isEditable()} for the definition
	 * when an album is editable.
	 *
	 * This method is mostly only useful during deletion of albums, when no
	 * album models are loaded for efficiency reasons.
	 * If an album model is required anyway (because it shall be edited),
	 * then first load the album once and use
	 * {@link AlbumQueryPolicy::isEditable()}
	 * instead in order to avoid several DB requests.
	 *
	 * @param User              $user
	 * @param array<int,string> $albumIDs
	 *
	 * @return bool
	 *
	 * @throws QueryBuilderException
	 */
	public function canDeleteById(User $user, array $albumIDs): bool
	{
		$albumIDs = $this->uniquify($albumIDs);
		$num_albums = count($albumIDs);

		if ($num_albums === 0) {
			return $user->may_upload;
		}

		if (
			BaseAlbumImpl::query()
			->whereIn('id', $albumIDs)
			->where('owner_id', '=', $user->id)
			->count() === $num_albums
		) {
			return $user->may_upload;
		}

		if (
			AccessPermission::query()
			->whereIn(APC::BASE_ALBUM_ID, $albumIDs)
			->where(APC::USER_ID, '=', $user->id)
			->where(APC::GRANTS_DELETE, '=', true)
			->count() === $num_albums
		) {
			return true;
		}

		return false;
	}

	/**
	 * Check if user can share selected album with to public.
	 *
	 * @param User           $user
	 * @param ?AbstractAlbum $abstractAlbum
	 *
	 * @return bool
	 */
	public function canShare(?User $user, ?AbstractAlbum $abstractAlbum): bool
	{
		// should not be the case, but well.
		if ($abstractAlbum === null) {
			return true;
		}

		if (Configs::getValueAsBool('share_button_visible')) {
			return true;
		}

		if (!$abstractAlbum instanceof BaseAlbum) {
			return false;
		}

		return $this->isOwner($user, $abstractAlbum);
	}

	/**
	 * Check if user can share selected album with another user.
	 *
	 * @param User                             $user
	 * @param AbstractAlbum|BaseAlbumImpl|null $abstractAlbum
	 *
	 * @return bool
	 */
	public function canShareWithUsers(User $user, AbstractAlbum|BaseAlbumImpl|null $abstractAlbum): bool
	{
		if ($user->may_upload !== true) {
			return false;
		}

		// If this is null, this means that we are looking at the list.
		if ($abstractAlbum === null) {
			return true;
		}

		if (!$abstractAlbum instanceof BaseAlbum && !$abstractAlbum instanceof BaseAlbumImpl) {
			return false;
		}

		return $this->isOwner($user, $abstractAlbum);
	}

	/**
	 * Check if user can share selected albums with other users.
	 * Only owner can share.
	 *
	 * @param User              $user
	 * @param array<int,string> $albumIDs
	 *
	 * @return bool
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function canShareById(User $user, array $albumIDs): bool
	{
		if (!$user->may_upload) {
			return false;
		}

		$albumIDs = $this->uniquify($albumIDs);
		$num_albums = count($albumIDs);

		if ($num_albums === 0) {
			return false;
		}

		if (
			BaseAlbumImpl::query()
			->whereIn('id', $albumIDs)
			->where('owner_id', '=', $user->id)
			->count() === $num_albums
		) {
			return true;
		}

		return false;
	}

	/**
	 * Check whether user can import from server.
	 * Only Admin can do that.
	 *
	 * @param User|null $user
	 *
	 * @return bool
	 */
	public function canImportFromServer(?User $user): bool
	{
		return false;
	}

	// The following methods are not to be called by Gate.

	/**
	 * Pushes an album onto the stack of unlocked albums.
	 *
	 * @param BaseAlbum|BaseAlbumImpl $album
	 */
	public function unlock(BaseAlbum|BaseAlbumImpl $album): void
	{
		Session::push(AlbumPolicy::UNLOCKED_ALBUMS_SESSION_KEY, $album->id);
	}

	/**
	 * Check whether the given album has previously been unlocked.
	 *
	 * @param BaseAlbum|BaseAlbumImpl $album
	 *
	 * @return bool
	 */
	public function isUnlocked(BaseAlbum|BaseAlbumImpl $album): bool
	{
		return in_array($album->id, self::getUnlockedAlbumIDs(), true);
	}

	/**
	 * @return string[]
	 */
	public static function getUnlockedAlbumIDs(): array
	{
		return Session::get(self::UNLOCKED_ALBUMS_SESSION_KEY, []);
	}

	/**
	 * Remove root and smart albums, as they get a pass.
	 * Make IDs unique as otherwise count will fail.
	 *
	 * @param array<int,string> $albumIDs
	 *
	 * @return array<int,string>
	 */
	private function uniquify(array $albumIDs): array
	{
		return array_diff(
			array_unique($albumIDs),
			array_keys(SmartAlbumType::values()),
			[null]
		);
	}
}
