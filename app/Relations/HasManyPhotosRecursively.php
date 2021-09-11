<?php

namespace App\Relations;

use App\Models\Album;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class HasManyPhotosRecursively extends HasManyPhotos
{
	public function __construct(Album $owningAlbum)
	{
		parent::__construct($owningAlbum);
	}

	/**
	 * Adds the constraints for single owning album to the base query.
	 *
	 * This method is called by the framework, if the related photos of a
	 * single albums are fetched.
	 */
	public function addConstraints(): void
	{
		$this->addEagerConstraints([$this->owningAlbum]);
	}

	/**
	 * Adds the constraints for a list of owning album to the base query.
	 *
	 * This method is called by the framework, if the related photos of a
	 * list of owning albums are fetched.
	 * The the unified result of the query is mapped to the specific albums
	 * by {@link HasManyPhotosRecursively::match()}.
	 *
	 * @param array $albums an array of {@link \App\Models\Album} whose photos are loaded
	 */
	public function addEagerConstraints(array $albums): void
	{
		if (count($albums) !== 1) {
			throw new \InvalidArgumentException('eagerly fetching all photos of an album is only implemented for a single album at once');
		}
		/** @var Album $album */
		$album = $albums[0];

		$this->photoAuthorisationProvider
			->applyVisibilityFilter($this->query)
			->whereHas('album', function (Builder $q) use ($album) {
				$q->where('_lft', '>=', $album->_lft)
					->where('_rgt', '<=', $album->_rgt);
			});
	}

	/**
	 * Maps a collection of eagerly fetched photos to the given owning albums.
	 *
	 * This method is called by the framework after the unified result of
	 * photos has been fetched by {@link HasManyPhotosRecursively::addEagerConstraints()}.
	 *
	 * @param array      $albums   the list of owning albums
	 * @param Collection $photos   collection of {@link Photo} models which needs to be mapped to the albums
	 * @param string     $relation the name of the relation
	 *
	 * @return array
	 */
	public function match(array $albums, Collection $photos, $relation): array
	{
		if (count($albums) !== 1) {
			throw new \InvalidArgumentException('eagerly fetching all photos of an album is only implemented for a single album at once');
		}
		/** @var Album $album */
		$album = $albums[0];

		$photos->sortBy(
			$album->sorting_col,
			SORT_NATURAL | SORT_FLAG_CASE,
			$album->sorting_order === 'DESC'
		);
		$album->setRelation($relation, $photos);

		return $albums;
	}
}
