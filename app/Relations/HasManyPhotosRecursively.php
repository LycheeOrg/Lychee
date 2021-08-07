<?php

namespace App\Relations;

use App\Actions\Albums\Extensions\PublicIds;
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
		// apply security filter : Do not leak pictures which are not ours
		// TODO: Figure out why we test for forbidden IDs here and use a negative test, while we use allowed IDs and a positive test everywhere else
		$forbiddenID = resolve(PublicIds::class)->getNotAccessible();

		$this->query
			->whereNotIn('photos.album_id', $forbiddenID)
			->where(function (Builder $q1) use ($albums) {
				/** @var Album $album */
				foreach ($albums as $album) {
					$q1->orWhereHas('album', function (Builder $q2) use ($album) {
						$q2->where('albums.id', '>=', $album->_lft)
							->where('albums.id', '<=', $album->_rgt);
					});
				}
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
		// The dictionary maps album IDs to the subset of photos which are
		// direct children
		$dictionary = [];
		/** @var Photo $photo */
		foreach ($photos as $photo) {
			$albumId = $photo->album_id;
			if (array_key_exists($albumId, $dictionary)) {
				$dictionary[$albumId][] = $photo;
			} else {
				$dictionary[$albumId] = [$photo];
			}
		}

		/** @var Album $album */
		foreach ($albums as $album) {
			$allPhotos = $this->related->newCollection();
			array_walk(
				$dictionary,
				function (array $photos, int $albumId) use ($album, $allPhotos): void {
					if ($album->_lft <= $albumId && $albumId <= $album->_rgt) {
						$allPhotos->merge($photos);
					}
				}
			);
			$allPhotos->sortBy(
				$album->sorting_col,
				SORT_NATURAL | SORT_FLAG_CASE,
				$album->sorting_order === 'DESC'
			);
			$album->setRelation($relation, $allPhotos);
		}

		return $albums;
	}
}
