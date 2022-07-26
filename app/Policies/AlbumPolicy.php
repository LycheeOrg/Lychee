<?php

namespace App\Policies;

use App\Auth\AlbumAuthorisationProvider;
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
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;

class AlbumPolicy
{
	use HandlesAuthorization;

	protected UserPolicy $userPolicy;

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
		if ($user?->isAdmin()) {
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
	public function own(?User $user, BaseAlbum $album): bool
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
	public function access(?User $user, ?AbstractAlbum $album): bool
	{
		if ($album === null) {
			return true;
		}

		if ($album instanceof BaseAlbum) {
			try {
				return
					$this->own($user, $album) ||
					($album->is_public === true && $album->password === null) ||
					($album->is_public === true && $this->unlocked($album)) ||
					($album->shared_with()->where('user_id', '=', $user?->id)->count() > 0);
			} catch (\InvalidArgumentException $e) {
				throw LycheeAssertionError::createFromUnexpectedException($e);
			}
		} elseif ($album instanceof BaseSmartAlbum) {
			return $this->see($user, $album);
		} else {
			// Should never happen
			return false;
		}
	}

	/**
	 * Check if an album is dowmloadable.
	 *
	 * @param User|null      $user
	 * @param BaseAlbum|null $baseAlbum
	 *
	 * @return bool
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function download(?User $user, ?BaseAlbum $baseAlbum): bool
	{
		if ($baseAlbum === null) {
			return Configs::getValueAsBool('downloadable');
		}

		return $this->own($user, $baseAlbum) ||
			$baseAlbum->is_downloadable;
	}

	/**
	 * Check whether the given album has previously been unlocked.
	 *
	 * @param BaseAlbum|BaseAlbumImpl $album
	 *
	 * @return bool
	 */
	public function unlocked(BaseAlbum|BaseAlbumImpl $album): bool
	{
		return in_array($album->id, $this->getUnlockedAlbumIDs(), true);
	}

	/**
	 * @return array
	 */
	private function getUnlockedAlbumIDs(): array
	{
		return Session::get(AlbumAuthorisationProvider::UNLOCKED_ALBUMS_SESSION_KEY, []);
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
	public function edit(User $user, ?AbstractAlbum $album): bool
	{
		if (!$this->userPolicy->upload($user)) {
			return false;
		}

		// The root album and smart albums get a pass
		return
			$album === null ||
			$album instanceof BaseSmartAlbum ||
			($album instanceof BaseAlbum && $this->own($user, $album));
	}

	/**
	 * Checks whether the designated albums are editable by the current user.
	 *
	 * See {@link AlbumAuthorisationProvider::isEditable()} for the definition
	 * when an album is editable.
	 *
	 * This method is mostly only useful during deletion of albums, when no
	 * album models are loaded for efficiency reasons.
	 * If an album model is required anyway (because it shall be edited),
	 * then first load the album once and use
	 * {@link AlbumAuthorisationProvider::isEditable()}
	 * instead in order to avoid several DB requests.
	 *
	 * @param User  $user
	 * @param array $albumIDs
	 *
	 * @return bool
	 *
	 * @throws QueryBuilderException
	 */
	public function editById(User $user, array $albumIDs): bool
	{
		if ($this->before($user, 'editById') === true) {
			return true;
		}

		if (!$this->userPolicy->upload($user)) {
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
	public function see(?User $user, BaseSmartAlbum $smartAlbum): bool
	{
		return ($user !== null && $this->userPolicy->upload($user)) ||
			$smartAlbum->is_public;
	}
}
