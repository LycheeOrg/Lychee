<?php

namespace App\Policies;

use App\Contracts\AbstractAlbum;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\LycheeAssertionError;
use App\Exceptions\Internal\QueryBuilderException;
use App\Factories\AlbumFactory;
use App\Models\BaseAlbumImpl;
use App\Models\Configs;
use App\Models\Extensions\BaseAlbum;
use App\Models\User;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Support\Facades\Session;

class AlbumPolicy extends BasePolicy
{
	public const UNLOCKED_ALBUMS_SESSION_KEY = 'unlocked_albums';

	public const CAN_SEE = 'canSee';
	public const CAN_ACCESS = 'canAccess';
	public const CAN_DOWNLOAD = 'canDownload';
	public const CAN_UPLOAD = 'canUpload';
	public const CAN_EDIT = 'canEdit';
	public const CAN_EDIT_ID = 'canEditById';
	public const CAN_SHARE_WITH_USERS = 'canShareWithUsers';
	public const CAN_IMPORT_FROM_SERVER = 'canImportFromServer';
	public const CAN_SHARE_ID = 'canShareById';

	/**
	 * This ensures that current album is owned by current user.
	 *
	 * @param User|null $user
	 * @param BaseAlbum $album
	 *
	 * @return bool
	 */
	private function isOwner(?User $user, BaseAlbum|BaseAlbumImpl $album): bool
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
			$smartAlbum->is_public;
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
	 *
	 * @throws LycheeAssertionError
	 */
	public function canAccess(?User $user, ?AbstractAlbum $album): bool
	{
		if ($album === null) {
			return true;
		}

		if ($album instanceof BaseAlbum) {
			try {
				return
					$this->isOwner($user, $album) ||
					($album->is_public && $album->password === null) ||
					($album->is_public && $this->isUnlocked($album)) ||
					($album->shared_with()->where('user_id', '=', $user?->id)->count() > 0);
			} catch (\InvalidArgumentException $e) {
				throw LycheeAssertionError::createFromUnexpectedException($e);
			}
		} elseif ($album instanceof BaseSmartAlbum) {
			return $this->canSee($user, $album);
		} else {
			// Should never happen
			return false;
		}
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
		$default = Configs::getValueAsBool('downloadable');
		// The root album always uses the global setting
		if ($abstractAlbum === null) {
			return $default;
		}

		// TODO: when download rights are assigned to albums, we add more logic can be added here.
		return ($abstractAlbum instanceof BaseSmartAlbum && $user !== null) ||
		($abstractAlbum instanceof BaseAlbum && $this->isOwner($user, $abstractAlbum) ||
		($abstractAlbum instanceof BaseAlbum && $abstractAlbum->shared_with()->where('user_id', '=', $user?->id)->count() > 0 && $default) ||
		$abstractAlbum->grants_download);
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
		if ($abstractAlbum === null) {
			return $user->may_upload;
		}

		// TODO: when upload rights are assigned to albums, more logic can be added here.
		return $user->may_upload;
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
	 * @param User                             $user
	 * @param AbstractAlbum|BaseAlbumImpl|null $album the album; `null` designates the root album
	 *
	 * @return bool
	 */
	public function canEdit(User $user, AbstractAlbum|BaseAlbumImpl|null $album): bool
	{
		if (!$user->may_upload) {
			return false;
		}

		// The root album and smart albums get a pass
		return
			$album === null ||
			$album instanceof BaseSmartAlbum ||
			(($album instanceof BaseAlbum || $album instanceof BaseAlbumImpl) && $this->isOwner($user, $album));
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
		if (!$user->may_upload) {
			return false;
		}

		// Remove root and smart albums, as they get a pass.
		// Make IDs unique as otherwise count will fail.
		$albumIDs = array_diff(
			array_unique($albumIDs),
			array_keys(AlbumFactory::BUILTIN_SMARTS),
			[null]
		);

		return
			count($albumIDs) === 0 ||
			BaseAlbumImpl::query()
			->whereIn('id', $albumIDs)
			->where('owner_id', $user->id)
			->count() === count($albumIDs);
	}

	/**
	 * Check if user can share selected album with another user.
	 *
	 * @param User|null     $user
	 * @param AbstractAlbum $abstractAlbum
	 *
	 * @return bool
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function canShareWithUsers(?User $user, ?AbstractAlbum $abstractAlbum): bool
	{
		if ($abstractAlbum === null) {
			return false;
		}

		if ($user?->may_upload !== true) {
			return false;
		}

		if (in_array($abstractAlbum->id, AlbumFactory::BUILTIN_SMARTS, true)) {
			return false;
		}

		return $abstractAlbum instanceof BaseAlbum && $this->isOwner($user, $abstractAlbum);
	}

	/**
	 * Check if user can share selected albums with other users.
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
		return $this->canEditById($user, $albumIDs);
	}

	/**
	 * Check whether user can import from server.
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
		return in_array($album->id, $this->getUnlockedAlbumIDs(), true);
	}

	/**
	 * @return string[]
	 */
	public function getUnlockedAlbumIDs(): array
	{
		return Session::get(self::UNLOCKED_ALBUMS_SESSION_KEY, []);
	}
}
