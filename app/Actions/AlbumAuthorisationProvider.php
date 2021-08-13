<?php

namespace App\Actions;

use App\Contracts\BaseAlbum;
use App\Contracts\BaseModelAlbum;
use App\Facades\AccessControl;
use App\Factories\AlbumFactory;
use App\Models\Album;
use App\Models\BaseModelAlbumImpl;
use App\Models\TagAlbum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;

/**
 * Class AlbumAuthorisationProvider.
 *
 * An album is called _accessible_ if the current user may browse into
 * it, i.e. if the current user may open it and see its content.
 * If the current user actually sees some content additionally depends
 * on whether the content itself is visible.
 */
class AlbumAuthorisationProvider
{
	const UNLOCKED_ALBUMS_SESSION_KEY = 'unlocked_albums';
	protected AlbumFactory $albumFactory;

	public function __construct(AlbumFactory $albumFactory)
	{
		$this->albumFactory = $albumFactory;
	}

	/**
	 * Restricts an album query to _visible_ albums.
	 *
	 * An album is called _visible_ if the current user is allowed to see the
	 * album (itself) within a listing or similar.
	 * An album is _visible_ if any of the following conditions hold
	 * (OR-clause)
	 *
	 *  - the user is an admin
	 *  - the user is the owner of the album
	 *  - the album is shared with the user and the album does not require a direct link
	 *  - the album is public and the album does not require a direct link
	 *
	 * @param Builder $query
	 *
	 * @return Builder
	 */
	public function applyVisibilityFilter(Builder $query): Builder
	{
		$this->failForWrongQueryModel($query);
		if (AccessControl::is_admin()) {
			return $query;
		}

		$userID = AccessControl::id();
		// We must wrap everything into an outer query to avoid any undesired
		// effects in case that the original query already contains an
		// "OR"-clause.
		return $query->where(
			function (Builder $query2) use ($userID) {
				$query2
					->where('owner_id', '=', $userID)
					->orWhere(fn (Builder $q) => $q
						->where('requires_link', '=', false)
						->whereHas(
							'shared_with',
							fn (Builder $q2) => $q2->where('user_id', '=', $userID)
						)
					)
					->orWhere(fn (Builder $q) => $q
						->where('requires_link', '=', false)
						->where('public', '=', true)
					);
			}
		);
	}

	/**
	 * Checks whether the album with the given ID is visible for the current
	 * user.
	 *
	 * For real albums (i.e. albums that are stored in the DB), see
	 * {@link AlbumAuthorisationProvider::applyVisibilityFilter()} for a
	 * specification of the rules when an album is visible.
	 * In other cases, the following holds:
	 *  - the root album is visible if and only if the user is authenticated
	 *  - the built-in smart albums are visible, if
	 *     - the user is authenticated and is granted the right of uploading, or
	 *     - the album is the album of recent photos and public by configuration, or
	 *     - the album is the album of starred photos and public by configuration
	 *
	 * @param string|int|BaseAlbum|null $album
	 *
	 * @return bool
	 */
	public function isVisible($album): bool
	{
		// deal with root album first
		if (empty($album)) {
			return AccessControl::is_logged_in();
		}

		/** @var ?BaseAlbum $album */
		/** @var int|string $albumID */
		list($albumID, $album) = $this->disassembleAlbumParameter($album);

		// Deal with built-in smart albums
		if ($this->albumFactory->isBuiltInSmartAlbum($albumID)) {
			return $this->isAuthorizedForSmartAlbum($albumID);
		}

		// Deal with albums that are real models.
		// If we already have a model, then avoid an unnecessary DB query.
		// If we don't have a model, then use `applyVisibilityFilter` to build
		// a query, but don't hydrate a model
		if ($album) {
			/** @var BaseModelAlbum $album */
			$userID = AccessControl::id();

			return
				($album->owner_id === $userID) ||
				(!$album->requires_link && $album->shared_with()->where('user_id', '=', $userID)->count()) ||
				(!$album->requires_link && $album->public);
		} else {
			return $this->applyVisibilityFilter(
				BaseModelAlbumImpl::query()->where('id', '=', intval($albumID))
			)->count();
		}
	}

	/**
	 * Restricts an album query to _accessible_ albums.
	 *
	 * An album is called _accessible_ if the current user is allowed to
	 * browse into it, i.e. if the current user may open it and see its
	 * content.
	 * An album is _accessible_ if any of the following conditions hold
	 * (OR-clause)
	 *
	 *  - the user is an admin
	 *  - the user is the owner of the album
	 *  - the album is shared with the user
	 *  - the album is public AND no password is set
	 *  - the album is public AND has been unlocked
	 *
	 * @param Builder $query
	 *
	 * @return Builder
	 */
	public function applyAccessibilityFilter(Builder $query): Builder
	{
		$this->failForWrongQueryModel($query);
		if (AccessControl::is_admin()) {
			return $query;
		}

		$userID = AccessControl::id();
		$unlockedAlbumIDs = $this->getUnlockedAlbumIDs();
		// We must wrap everything into an outer query to avoid any undesired
		// effects in case that the original query already contains an
		// "OR"-clause.
		return $query->where(
			function (Builder $query2) use ($unlockedAlbumIDs, $userID) {
				$query2
					->where('owner_id', '=', $userID)
					->orWhereHas(
						'shared_with',
						fn (Builder $q) => $q->where('user_id', '=', $userID)
					)
					->orWhere(fn (Builder $q) => $q
						->where('public', '=', true)
						->whereNull('password')
					)
					->orWhere(fn (Builder $q) => $q
						->where('public', '=', true)
						->whereIn('id', $unlockedAlbumIDs)
					);
			}
		);
	}

	/**
	 * Checks whether the album with the given ID is visible for the current
	 * user.
	 *
	 * For real albums (i.e. albums that are stored in the DB), see
	 * {@link AlbumAuthorisationProvider::applyVisibilityFilter()} for a
	 * specification of the rules when an album is visible.
	 * In other cases, the following holds:
	 *  - the root album is visible if and only if the user is authenticated
	 *  - the built-in smart albums are visible, if
	 *     - the user is authenticated and is granted the right of uploading, or
	 *     - the album is the album of recent photos and public by configuration, or
	 *     - the album is the album of starred photos and public by configuration
	 *
	 * @param string|int|BaseAlbum|null $album
	 *
	 * @return bool
	 */
	public function isAccessible($album): bool
	{
		// deal with root album first
		if (empty($album)) {
			return AccessControl::is_logged_in();
		}

		/** @var ?BaseAlbum $album */
		/** @var int|string $albumID */
		list($albumID, $album) = $this->disassembleAlbumParameter($album);

		// Deal with built-in smart albums
		if ($this->albumFactory->isBuiltInSmartAlbum($albumID)) {
			return $this->isAuthorizedForSmartAlbum($albumID);
		}

		// Deal with albums that are real models.
		// If we already have a model, then avoid an unnecessary DB query.
		// If we don't have a model, then use `applyVisibilityFilter` to build
		// a query, but don't hydrate a model
		if ($album) {
			/** @var BaseModelAlbum $album */
			$userID = AccessControl::id();

			return
				($album->owner_id === $userID) ||
				($album->shared_with()->where('user_id', '=', $userID)->count()) ||
				($album->public && $album->password === null) ||
				($album->public && $this->isAlbumUnlocked($album->id));
		} else {
			return $this->applyAccessibilityFilter(
				BaseModelAlbumImpl::query()->where('id', '=', intval($albumID))
			)->count();
		}
	}

	/**
	 * Pushes an album ID onto the stack of unlocked albums.
	 *
	 * @param int $albumID
	 */
	public function unlockAlbum(int $albumID): void
	{
		Session::push(self::UNLOCKED_ALBUMS_SESSION_KEY, $albumID);
	}

	/**
	 * Check if the given album ID has previously been unlocked.
	 *
	 * @param int $albumID
	 *
	 * @return bool
	 */
	public function isAlbumUnlocked(int $albumID): bool
	{
		return in_array($albumID, $this->getUnlockedAlbumIDs());
	}

	private function getUnlockedAlbumIDs(): array
	{
		return Session::get(self::UNLOCKED_ALBUMS_SESSION_KEY, []);
	}

	/**
	 * Throws an exception if the given query does not query for an album.
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @param Builder $query
	 */
	private function failForWrongQueryModel(Builder $query): void
	{
		$model = $query->getModel();
		if (!(
			$model instanceof BaseModelAlbumImpl ||
			$model instanceof Album ||
			$model instanceof TagAlbum
		)) {
			throw new \InvalidArgumentException('the given query must query for album');
		}
	}

	/**
	 * This method sorts the passed multi-typed parameter into the correct
	 * return type.
	 *
	 * This method returns a pair [albumID, album] acc. to the following rules
	 *  - if `$in === null` is passed in, the result is `[0, null]`
	 *  - if an ID is passed in, i.e. if `$in` is an integer or string, the
	 *    result is `[$in, null]`, i.e. the input parameter is returned as
	 *    the ID of an album
	 *  - if an albums is passed in, i.e. if `$in` is an instance of
	 *    {@link BaseAlbum}, then the result is `[$in->id, $in]`, i.e. the
	 *   input parameter is returned as the album and the ID is extracted.
	 *
	 * Note, this method never loads any model from database.
	 *
	 * @param string|int|BaseAlbum|null $in
	 *
	 * @return array an array with [albumID, album]
	 */
	private function disassembleAlbumParameter($in): array
	{
		if ($in instanceof BaseAlbum) {
			return [$in->id, $in];
		} else {
			return [$in ?: 0, null];
		}
	}

	/**
	 * This is the common code to decide whether the given smart album is
	 * visible/accessible by the current user.
	 *
	 * Note, that the logic for visibility and/or accessibility of a smart
	 * album is identical.
	 *
	 * @param string $smartAlbumID
	 *
	 * @return bool true, if the smart album is visible/accessible by the user
	 */
	private function isAuthorizedForSmartAlbum(string $smartAlbumID): bool
	{
		return
			(AccessControl::is_logged_in() && AccessControl::can_upload()) ||
			$this->albumFactory->createSmartAlbum($smartAlbumID)->public;
	}
}
