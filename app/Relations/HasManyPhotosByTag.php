<?php

namespace App\Relations;

use App\Models\TagAlbum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class HasManyPhotosByTag extends HasManyPhotos
{
	public function __construct(TagAlbum $owningAlbum)
	{
		parent::__construct($owningAlbum);
	}

	/**
	 * Adds the constraints for single owning album to the base query.
	 *
	 * This method is called by the framework, if the photos of a
	 * single tag albums are fetched.
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
	 * by {@link HasManyPhotosByTag::match()}.
	 *
	 * @param array $albums an array of {@link \App\Models\TagAlbum} whose photos are loaded
	 */
	public function addEagerConstraints(array $albums): void
	{
		$this->applyVisibilityFilter($this->query)
			->where(function (Builder $q1) use ($albums) {
				/** @var TagAlbum $album */
				foreach ($albums as $album) {
					$q1->orWhere(function (Builder $q2) use ($album) {
						// Filter for requested tags
						$tags = explode(',', $album->show_tags);
						foreach ($tags as $tag) {
							$q2->where('tags', 'like', '%' . trim($tag) . '%');
						}
					});
				}
			});
	}

	/**
	 * Maps a collection of eagerly fetched photos to the given owning albums.
	 *
	 * This method is called by the framework after the unified result of
	 * photos has been fetched by {@link HasManyPhotosByTag::addEagerConstraints()}.
	 *
	 * @param array      $models   the list of owning albums
	 * @param Collection $photos   collection of {@link Photo} models which needs to be mapped to the albums
	 * @param string     $relation the name of the relation
	 *
	 * @return array
	 */
	public function match(array $models, Collection $photos, $relation): array
	{
		// TODO: Implement match() method.
		throw new \BadMethodCallException('not implemented yet');
	}
}
