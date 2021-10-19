<?php

namespace App\Relations;

use App\Actions\AlbumAuthorisationProvider;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Collection;

class HasManyPhotosRecursively extends HasManyPhotos
{
	protected AlbumAuthorisationProvider $albumAuthorisationProvider;
	protected $emptyResult = false;

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
	 * @param array<Album> $albums an array of {@link \App\Models\Album} whose photos are loaded
	 */
	public function addEagerConstraints(array $albums): void
	{
		if (count($albums) !== 1) {
			throw new \InvalidArgumentException('eagerly fetching all photos of an album is only implemented for a single album at once');
		}

		if ($this->albumAuthorisationProvider->isAccessible($albums[0])) {
			$this->photoAuthorisationProvider
				->applySearchabilityFilter($this->query, $albums[0]);
		} else {
			// If $albums[0] is not accessible, then the relation has to return
			// an empty result.
			// We explicitly keep track of this case to by-bass an actual DB query.
			// See {@link get()} and {@link getResults()}.
			$this->emptyResult = true;
			// The next line is just a safety measure, in case someone does
			// not call the native methods of the relation class, but tries to
			// by-pass the relation class and to invoke the underlying query
			// directly.
			$this->query = $this->related->newModelQuery()->whereRaw('1 = 0');
		}
	}

	/**
	 * @param array|string[] $columns
	 *
	 * @return Collection<Photo>
	 */
	public function get($columns = ['*']): Collection
	{
		return $this->emptyResult ? $this->related->newCollection() : parent::get($columns);
	}

	public function getResults(): Collection
	{
		return $this->emptyResult ? $this->related->newCollection() : parent::getResults();
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
