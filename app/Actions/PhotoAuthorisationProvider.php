<?php

namespace App\Actions;

use App\Exceptions\Internal\InvalidQueryModelException;
use App\Facades\AccessControl;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;

class PhotoAuthorisationProvider
{
	protected AlbumAuthorisationProvider $albumAuthorisationProvider;

	public function __construct()
	{
		$this->albumAuthorisationProvider = resolve(AlbumAuthorisationProvider::class);
	}

	/**
	 * Restricts a photo query to _public_ photos.
	 *
	 * A photo is called _public_ if an anonymous (unauthenticated) user is
	 * allowed to see it.
	 * A photo is _visible_ if any of the following conditions hold
	 * (OR-clause):
	 *
	 *  - the photo itself is public on its own right
	 *  - the photo is part of a public album
	 *
	 * Note: This query also included photos which are part of public, but
	 * password-protected albums.
	 * Do we really want this?
	 * At the moment this method is used by
	 * {@link \App\Actions\RSS\Generate::do()}.
	 * Hence, this might leak thumbnails of photos to individuals who have
	 * subscribed to the RSS feed, but do not know the correct password for
	 * a password-protected album.
	 * Moreover, this filter does not take the configuration parameter
	 * `public_photos_hidden` into account.
	 * In summary, the result of this filter is very different from the
	 * result of {@link PhotoAuthorisationProvider::applyVisibilityFilter()}
	 * for an anonymous user.
	 * However, the logic here resembles the old behaviour.
	 * TODO: Re-consider if we really want it this way?
	 */
	public function applyPublicFilter(Builder $query): Builder
	{
		$this->failForWrongQueryModel($query);

		return $query->where(fn (Builder $q) => $q
			->where('is_public', '=', true)
			->orWhereHas('album', fn (Builder $q2) => $q2->where('is_public', '=', true))
		);
	}

	/**
	 * Restricts a photo query to _visible_ photos.
	 *
	 * A photo is called _visible_ if the current user is allowed to see the
	 * photo.
	 * A photo is _visible_ if any of the following conditions hold
	 * (OR-clause):
	 *
	 *  - the user is the admin
	 *  - the user is the owner of the photo
	 *  - the photo is part of an album which the user is allowed to access
	 *    (cp. {@link AlbumAuthorisationProvider::applyAccessibilityFilter()}.
	 *  - the photo is unsorted (not part of any album) and the user is granted
	 *    the right to upload photos
	 *  - the photo is public and public photos are not hidden by config
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

		if (!AccessControl::is_logged_in()) {
			// We must wrap everything into an outer query to avoid any undesired
			// effects in case that the original query already contains an
			// "OR"-clause.
			return $query->where(
				function (Builder $query2) {
					$query2->whereHas('album', fn (Builder $q) => $this->albumAuthorisationProvider->applyAccessibilityFilter($q));
					if (Configs::get_value('public_photos_hidden', '1') === '0') {
						$query2->orWhere('is_public', '=', true);
					}
				}
			);
		}

		$userID = AccessControl::id();

		// We must wrap everything into an outer query to avoid any undesired
		// effects in case that the original query already contains an
		// "OR"-clause.
		return $query->where(
			function (Builder $query2) use ($userID) {
				$query2->where('owner_id', '=', $userID);
				$query2->orWhereHas('album', fn (Builder $q) => $this->albumAuthorisationProvider->applyAccessibilityFilter($q));
				if (AccessControl::can_upload()) {
					$query2->orWhereNull('album_id');
				}
				if (Configs::get_value('public_photos_hidden', '1') === '0') {
					$query2->orWhere('is_public', '=', true);
				}
			}
		);
	}

	/**
	 * Checks whether the photo is accessible by the current user.
	 *
	 * See {@link PhotoAuthorisationProvider::applyVisibilityFilter()} for a
	 * specification of the rules when a photo is visible.
	 *
	 * Note, this method tries to minimize DB queries and any overhead due
	 * to hydration of models.
	 * If an actual instance of a {@link Photo} model is passed in, then the
	 * DB won't be queried at all, because all checks are performed on the
	 * values of the already hydrated model.
	 * If an ID is passed, then the method runs a very efficient COUNT
	 * query on the DB.
	 * In particular, no {@link Photo} model is hydrated to avoid any
	 * overhead.
	 *
	 * Tips for usage:
	 *  - If you already have a {@link Photo} instance, pass that.
	 *    This is most efficient.
	 *  - If you do not have a {@link Photo} instance, but you will need one
	 *    later anyway, then first fetch the photo from DB and pass the photo.
	 *    This avoids a second DB query later.
	 *  - If you do not have a {@link Photo} instance, and you won't need one
	 *    later, simply pass the ID of the photo.
	 *    This avoids the overhead of model hydration.
	 *
	 * @param int|Photo $photoModelOrID
	 *
	 * @return bool
	 */
	public function isVisible($photoModelOrID): bool
	{
		if (AccessControl::is_admin()) {
			return true;
		}

		/** @var ?Photo $photo */
		/** @var int $photoID */
		list($photoID, $photo) = $this->disassemblePhotoParameter($photoModelOrID);

		// If we already have an instance of a model, then avoid an
		// unnecessary DB query.
		// We perform the accessibility checks directly on the photo model.
		// The semantics of these checks must be kept in sync with the
		// checks in `applyVisibilityFilter`.
		if ($photo) {
			// Again, avoid unnecessary DB queries
			// If the album of the photo has already been loaded, we pass
			// the instance of the model to AlbumAuthorisationProvider.
			// (Then AlbumAuthorisationProvider won't query the DB at all.)
			// If the album has not yet been loaded, we pass the ID of the
			// album.
			// (Then AlbumAuthorisationProvider must query the DB, but
			// still avoids hydrating an actual model.)
			$albumModelOrID = $photo->relationLoaded('album') ? $photo->album : $photo->album_id;
			if (!AccessControl::is_logged_in()) {
				return
					($photo->is_public && Configs::get_value('public_photos_hidden', '1') === '0') ||
					$this->albumAuthorisationProvider->isAccessible($albumModelOrID);
			} else {
				return
					AccessControl::is_current_user($photo->owner_id) ||
					(AccessControl::can_upload() || empty($albumModelOrID)) ||
					($photo->is_public && Configs::get_value('public_photos_hidden', '1') === '0') ||
					$this->albumAuthorisationProvider->isAccessible($albumModelOrID);
			}
		} else {
			// If we don't have an instance of a model, then use
			// `applyVisibilityFilter` to build a query, but don't hydrate a
			// model
			return $this->applyVisibilityFilter(
				Photo::query()->where('id', '=', $photoID)
			)->count() !== 0;
		}
	}

	/**
	 * Checks whether the photos with the given IDs are editable by the
	 * current user.
	 *
	 * A photo is called _editable_ if the current user is allowed to edit
	 * the photo's properties.
	 * An photo is _editable_ if any of the following conditions hold
	 * (OR-clause)
	 *
	 *  - the user is an admin
	 *  - the user is the owner of the photo
	 *
	 * @param int[] $photoIDs
	 *
	 * @return bool
	 */
	public function areEditable(array $photoIDs): bool
	{
		if (AccessControl::is_admin()) {
			return true;
		}
		if (!AccessControl::is_logged_in()) {
			return false;
		}

		$userID = AccessControl::id();
		// Since we count the result we need to ensure that there are no
		// duplicates.
		$photoIDs = array_unique($photoIDs);
		if (count($photoIDs) > 0) {
			return Photo::query()
				->whereIn('id', $photoIDs)
				->where('owner_id', '=', $userID)
				->count() === count($photoIDs);
		}

		return true;
	}

	/**
	 * Throws an exception if the given query does not query for a photo.
	 *
	 * @throws InvalidQueryModelException
	 *
	 * @param Builder $query
	 */
	private function failForWrongQueryModel(Builder $query): void
	{
		$model = $query->getModel();
		if (!($model instanceof Photo)) {
			throw new InvalidQueryModelException('photo');
		}
	}

	/**
	 * This method sorts the passed multi-typed parameter into the correct
	 * return type.
	 *
	 * This method returns a pair [photoID, photo] acc. to the following rules
	 *  - if an ID is passed in, i.e. if `$in` is an integer or string, the
	 *    result is `[$in, null]`, i.e. the input parameter is returned as
	 *    the ID of an album
	 *  - if a photo is passed in, i.e. if `$in` is an instance of
	 *    {@link Photo}, then the result is `[$in->id, $in]`, i.e. the
	 *    input parameter is returned as the photo and the ID is extracted.
	 *
	 * Note, this method never loads any model from database.
	 *
	 * @param int|Photo $in
	 *
	 * @return array an array with [photoID, photo]
	 */
	private function disassemblePhotoParameter($in): array
	{
		if ($in instanceof Photo) {
			return [$in->id, $in];
		} else {
			return [$in, null];
		}
	}
}
