<?php

namespace App\Relations;

use App\Actions\AlbumAuthorisationProvider;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Collection;

class HasManyPhotosRecursively extends HasManyPhotos
{
	protected AlbumAuthorisationProvider $albumAuthorisationProvider;

	public function __construct(Album $owningAlbum)
	{
		// Sic! We must initialize attributes of this class before we call
		// the parent constructor.
		// The parent constructor calls `addConstraints` and thus our own
		// attributes must be initialized by then
		$this->albumAuthorisationProvider = resolve(AlbumAuthorisationProvider::class);
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
		if (static::$constraints) {
			$this->addEagerConstraints([$this->parent]);
		}
	}

	/**
	 * Adds the constraints for a list of owning album to the base query.
	 *
	 * This method is called by the framework, if the related photos of a
	 * list of owning albums are fetched.
	 * The unified result of the query is mapped to the specific albums
	 * by {@link HasManyPhotosRecursively::match()}.
	 *
	 * @param array<Album> $albums an array of {@link \App\Models\Album} whose photos are loaded
	 */
	public function addEagerConstraints(array $albums): void
	{
		if (count($albums) !== 1) {
			throw new \InvalidArgumentException('eagerly fetching all photos of an album is only implemented for a single album at once');
		}

		$this->photoAuthorisationProvider
			->applySearchabilityFilter($this->query, $albums[0]);
	}

	public function getResults(): Collection
	{
		/** @var Album $album */
		$album = $this->parent;
		if ($album === null || !$this->albumAuthorisationProvider->isAccessible($album)) {
			return $this->related->newCollection();
		} else {
			return parent::getResults();
		}
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

		if (!$this->albumAuthorisationProvider->isAccessible($album)) {
			$album->setRelation($relation, $this->related->newCollection());
		} else {
			$photos = $photos->sortBy(
				$album->sorting_col,
				SORT_NATURAL | SORT_FLAG_CASE,
				$album->sorting_order === 'DESC'
			)->values();
			$album->setRelation($relation, $photos);
		}

		return $albums;
	}
}
