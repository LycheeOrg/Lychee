<?php

namespace App\Relations;

use App\Actions\AlbumAuthorisationProvider;
use App\Facades\AccessControl;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class HasManyChildPhotos extends HasManyBidirectionally
{
	protected AlbumAuthorisationProvider $albumAuthorisationProvider;

	public function __construct(Album $owningAlbum)
	{
		// Sic! We must initialize attributes of this class before we call
		// the parent constructor.
		// The parent constructor calls `addConstraints` and thus our own
		// attributes must be initialized by then
		$this->albumAuthorisationProvider = resolve(AlbumAuthorisationProvider::class);
		parent::__construct(
			Photo::query(),
			$owningAlbum,
			'album_id',
			'id',
			'album'
		);
	}

	public function addConstraints()
	{
		if (static::$constraints) {
			parent::addConstraints();
			$this->applyVisibilityFilter($this->query);
		}
	}

	public function addEagerConstraints(array $models)
	{
		parent::addEagerConstraints($models);
		$this->applyVisibilityFilter($this->query);
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
	 *  - the photo is unsorted (not part of any album) and the user is granted the right to upload photos
	 *  - the photo is public and public photos are not excluded from search results
	 *
	 * TODO: Move this method into a `PhotoAuthorizationProvider` in the same spirit as `AlbumAuthorizationProvider`.
	 *
	 * TODO: This method is a duplicate of {@link \App\Actions\Search\PhotoSearch::applyVisibilityFilter()}.
	 *
	 * @param Builder $query
	 *
	 * @return Builder
	 */
	protected function applyVisibilityFilter(Builder $query): Builder
	{
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
						$query2->orWhere('public', '=', true);
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
					$query2->orWhere('public', '=', true);
				}
			}
		);
	}

	public function getResults()
	{
		if (is_null($this->getParentKey())) {
			return $this->related->newCollection();
		}

		$sortingCol = $this->parent->sorting_col;
		$sortingOrder = $this->parent->sorting_order;

		if (in_array($sortingCol, ['title', 'description'])) {
			return $this->query
				->orderBy('id', 'ASC')
				->get()
				->sortBy($sortingCol, SORT_NATURAL | SORT_FLAG_CASE, $sortingOrder === 'DESC');
		} else {
			return $this->query
				->orderBy($sortingCol, $sortingOrder)
				->orderBy('id', 'ASC')
				->get();
		}
	}

	/**
	 * Match the eagerly loaded results to their parents.
	 *
	 * @param array      $models   an array of parent models
	 * @param Collection $results  the unified collection of all child models of all parent models
	 * @param string     $relation the name of the relation from the parent to the child models
	 *
	 * @return array
	 */
	public function match(array $models, Collection $results, $relation): array
	{
		$dictionary = $this->buildDictionary($results);

		// Once we have the dictionary we can simply spin through the parent models to
		// link them up with their children using the keyed dictionary to make the
		// matching very convenient and easy work. Then we'll just return them.
		/** @var Album $model */
		foreach ($models as $model) {
			if (isset($dictionary[$key = $this->getDictionaryKey($model->getAttribute($this->localKey))])) {
				/** @var Collection $childrenOfModel */
				$childrenOfModel = $this->getRelationValue($dictionary, $key, 'many');
				$childrenOfModel = $childrenOfModel->sortBy($model->sorting_col, SORT_NATURAL | SORT_FLAG_CASE, $model->sorting_order === 'DESC');
				$model->setRelation($relation, $childrenOfModel);
				// This is the newly added code which sets this method apart
				// from the original method and additionally sets the
				// reverse link
				/** @var Model $childModel */
				foreach ($childrenOfModel as $childModel) {
					$childModel->setRelation($this->foreignMethodName, $model);
				}
			}
		}

		return $models;
	}
}
