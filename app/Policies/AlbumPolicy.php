<?php

namespace App\Policies;

use App\Contracts\AbstractAlbum;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\LycheeAssertionError;
use App\Exceptions\Internal\QueryBuilderException;
use App\Factories\AlbumFactory;
use App\Models\BaseAlbumImpl;
use App\Models\Configs;
use App\Models\Extensions\BaseAlbum;
use App\Models\User;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Session;

class AlbumPolicy
{
	use HandlesAuthorization;

	protected UserPolicy $userPolicy;

	public const UNLOCKED_ALBUMS_SESSION_KEY = 'unlocked_albums';

	// constants to be used in GATE
	public const IS_OWNER = 'isOwner';
	public const CAN_ACCESS = 'canAccess';
	public const CAN_DOWNLOAD = 'canDownload';
	public const CAN_EDIT = 'canEdit';
	public const IS_VISIBLE = 'isVisible';
	public const CAN_EDIT_ID = 'canEditById';

	/**
	 * @throws FrameworkException
	 */
	public function __construct()
	{
		try {
			$this->userPolicy = resolve(UserPolicy::class);
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s provider component', $e);
		}
	}

	/**
	 * Perform pre-authorization checks.
	 *
	 * @param \App\Models\User $user
	 * @param string           $ability
	 *
	 * @return void|bool
	 */
	public function before(?User $user, $ability)
	{
		if ($this->userPolicy->isAdmin($user)) {
			return true;
		}
	}

	/**
	 * This gate policy ensures that the Album is owned by current user.
	 * Do note that in case of current user being admin, it will be skipped due to the before method.
	 *
	 * @param User|null $user
	 * @param BaseAlbum $album
	 *
	 * @return bool
	 */
	public function isOwner(?User $user, BaseAlbum $album): bool
	{
		return $user !== null && $album->owner_id === $user->id;
	}

	/**
	 * Checks whether the album is accessible by the current user.
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
			return $this->isVisible($user, $album);
		} else {
			// Should never happen
			return false;
		}
	}

	/**
	 * Check if an album is downloadable.
	 *
	 * @param User|null      $user
	 * @param BaseAlbum|null $baseAlbum
	 *
	 * @return bool
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function canDownload(?User $user, ?BaseAlbum $baseAlbum): bool
	{
		if ($baseAlbum === null) {
			return Configs::getValueAsBool('downloadable');
		}

		return $this->isOwner($user, $baseAlbum) ||
			$baseAlbum->is_downloadable;
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
	public function canEdit(User $user, ?AbstractAlbum $album): bool
	{
		if (!$this->userPolicy->canUpload($user)) {
			return false;
		}

		// The root album and smart albums get a pass
		return
			$album === null ||
			$album instanceof BaseSmartAlbum ||
			($album instanceof BaseAlbum && $this->isOwner($user, $album));
	}

	/**
	 * Checks whether the album is visible by the current user.
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
	public function isVisible(?User $user, BaseSmartAlbum $smartAlbum): bool
	{
		return ($user !== null && $this->userPolicy->canUpload($user)) ||
			$smartAlbum->is_public;
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
	 * @param User  $user
	 * @param array $albumIDs
	 *
	 * @return bool
	 *
	 * @throws QueryBuilderException
	 */
	public function canEditById(User $user, array $albumIDs): bool
	{
		if (!$this->userPolicy->canUpload($user)) {
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
