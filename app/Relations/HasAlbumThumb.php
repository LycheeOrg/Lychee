<?php

namespace App\Relations;

use App\Actions\AlbumAuthorisationProvider;
use App\Actions\PhotoAuthorisationProvider;
use App\Facades\AccessControl;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Extensions\Thumb;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as BaseBuilder;

class HasAlbumThumb extends Relation
{
	protected AlbumAuthorisationProvider $albumAuthorisationProvider;
	protected PhotoAuthorisationProvider $photoAuthorisationProvider;

	public function __construct(Album $parent)
	{
		// Sic! We must initialize attributes of this class before we call
		// the parent constructor.
		// The parent constructor calls `addConstraints` and thus our own
		// attributes must be initialized by then
		$this->albumAuthorisationProvider = resolve(AlbumAuthorisationProvider::class);
		$this->photoAuthorisationProvider = resolve(PhotoAuthorisationProvider::class);
		parent::__construct(
			Photo::query()->with(['size_variants_raw', 'size_variants_raw.sym_links']),
			$parent
		);
	}

	public function addConstraints(): void
	{
		if (static::$constraints) {
			/** @var Album $album */
			$album = $this->parent;
			$this->photoAuthorisationProvider
				->applySearchabilityFilter($this->query, $album);
		}
	}

	/**
	 * Todo.
	 *
	 * The query used in the method is wrong.
	 * It does neither order nor limit the `JOIN`-clause.
	 * Here is a corrected version (without the WHERE `IN`-clause).
	 *
	 *     SELECT
	 *       album_cover.album_id AS album_id,
	 *       album_cover.cover_id AS cover_id,
	 *       photos.type AS cover_type
	 *     FROM (
	 *       SELECT
	 *         covered_albums.id AS album_id, (
	 *           SELECT p.id
	 *           FROM photos AS p
	 *           LEFT JOIN albums AS a ON (a.id = p.album_id)
	 *           WHERE a._lft >= covered_albums._lft AND a._rgt <= covered_albums._rgt
	 *           ORDER BY p.is_starred DESC, p.created_at DESC
	 *           LIMIT 1
	 *         ) AS cover_id
	 *       FROM albums AS covered_albums
	 *     ) AS album_cover
	 *     LEFT JOIN photos ON (photos.id = album_cover.cover_id);.
	 *
	 * @param array<Album> $models
	 */
	public function addEagerConstraints(array $models): void
	{
		$albumKeys = $this->getKeys($models);

		if (AccessControl::is_admin()) {
			$bestChildPhoto = function (BaseBuilder $builder) use ($albumKeys) {
				$builder
					->from('albums as covered_albums')
					->select(['covered_albums.id AS album_id'])
					->addSelect(['photo_id' => Photo::query()
						->from('photos')
						->select(['photos.id AS photo_id'])
						->leftJoin('albums', 'albums.id', '=', 'photos.album_id')
						->whereColumn('albums._lft', '>=', 'covered_albums._lft')
						->whereColumn('albums._rgt', '<=', 'covered_albums._rgt')
						->orderBy('photos.is_starred', 'desc')
						->orderBy('photos.created_at', 'desc')
						->limit(1),
					])
					->whereIn('covered_albums.id', $albumKeys);
			};
		} else {
			$userID = AccessControl::is_logged_in() ? AccessControl::id() : null;
			$maySearchPublic = Configs::get_value('public_photos_hidden', '1') !== '1';
			$unlockedAlbumIDs = [];

			// TODO: Use the searchability/browsability methods here. To this end the parameter `origin` needs to support outer columns
			$bestChildPhoto = function (BaseBuilder $builder) use ($albumKeys, $userID, $maySearchPublic, $unlockedAlbumIDs) {
				$builder
					->from('albums as covered_albums')
					->select(['covered_albums.id AS album_id'])
					->addSelect(['photo_id' => Photo::query()
						->from('photos')
						->select(['photos.id AS photo_id'])
						->leftJoin('albums', 'albums.id', '=', 'photos.album_id')
						->whereColumn('albums._lft', '>=', 'covered_albums._lft')
						->whereColumn('albums._rgt', '<=', 'covered_albums._rgt')
						->where(function ($query2) use ($userID, $maySearchPublic, $unlockedAlbumIDs) {
							$query2->whereNotExists(function (BaseBuilder $query3) use ($userID, $unlockedAlbumIDs) {
								$query3
									->from('albums', 'inner')
									->join('base_albums as inner_base_albums', 'inner_base_albums.id', '=', 'inner.id')
									->whereColumn('inner._lft', '>', 'covered_albums._lft')
									->whereColumn('inner._rgt', '<', 'covered_albums._rgt')
									->whereColumn('inner._lft', '<=', 'albums._lft')
									->whereColumn('inner._rgt', '>=', 'albums._rgt')
									->where(fn (BaseBuilder $q) => $q
										->where('inner_base_albums.requires_link', '=', true)
										->orWhere('inner_base_albums.is_public', '=', false)
										->orWhereNotNull('inner_base_albums.password')
									)
									->where(fn (BaseBuilder $q) => $q
										->where('inner_base_albums.requires_link', '=', true)
										->orWhere('inner_base_albums.is_public', '=', false)
										->orWhereNotIn('inner_base_albums.id', $unlockedAlbumIDs)
									);
								if ($userID !== null) {
									$query3
										->where('inner_base_albums.owner_id', '<>', $userID)
										->where(fn (BaseBuilder $q) => $q
											->where('inner_base_albums.requires_link', '=', true)
											->orWhereNotExists(fn (BaseBuilder $q2) => $q2
												->from('user_base_album', 'user_inner_base_album')
												->whereColumn('user_inner_base_album.base_album_id', '=', 'inner_base_albums.id')
												->where('user_inner_base_album.user_id', '=', $userID)
											)
										);
								}
							});
							if ($maySearchPublic) {
								$query2->orWhere('photos.is_public', '=', true);
							}
							if ($userID !== null) {
								$query2->orWhere('photos.owner_id', '=', $userID);
							}
						})
						->orderBy('photos.is_starred', 'desc')
						->orderBy('photos.created_at', 'desc')
						->limit(1),
					])
					->whereIn('covered_albums.id', $albumKeys);
			};
		}

		$this->query
			->select([
				'covers.id as id',
				'covers.type as type',
				'best_child_photo.album_id as covered_album_id',
			])
			->from($bestChildPhoto, 'best_child_photo')
			->join(
				'photos as covers',
				'covers.id',
				'=',
				'best_child_photo.photo_id'
			);
	}

	/**
	 * @param array<Album> $models   an array of albums models whose thumbnails shall be initialized
	 * @param string       $relation the name of the relation from the parent to the child models
	 *
	 * @return array the array of album models
	 */
	public function initRelation(array $models, $relation): array
	{
		foreach ($models as $model) {
			$model->setRelation($relation, null);
		}

		return $models;
	}

	/**
	 * Match the eagerly loaded results to their parents.
	 *
	 * @param array<Album>      $models   an array of parent models
	 * @param Collection<Photo> $results  the unified collection of all child models of all parent models
	 * @param string            $relation the name of the relation from the parent to the child models
	 *
	 * @return array
	 */
	public function match(array $models, Collection $results, $relation): array
	{
		$dictionary = $results->mapToDictionary(function ($result) {
			return [$result->covered_album_id => $result];
		})->all();

		// Once we have the dictionary we can simply spin through the parent models to
		// link them up with their children using the keyed dictionary to make the
		// matching very convenient and easy work. Then we'll just return them.
		/** @var Album $album */
		foreach ($models as $album) {
			$albumID = $album->id;
			if (isset($dictionary[$albumID])) {
				/** @var Photo $cover */
				$cover = reset($dictionary[$albumID]);
				$album->setRelation($relation, Thumb::createFromPhoto($cover));
			} else {
				$album->setRelation($relation, null);
			}
		}

		return $models;
	}

	public function getResults(): ?Thumb
	{
		/** @var Album $album */
		$album = $this->parent;
		if ($album === null || !$this->albumAuthorisationProvider->isAccessible($album)) {
			return null;
		}

		/** @var Photo|null $cover */
		$cover = $this->query
			->select(['photos.id', 'photos.type'])
			->orderBy('photos.is_starred', 'desc')
			->orderBy('photos.' . $album->sorting_col, $album->sorting_order)
			->first();

		return Thumb::createFromPhoto($cover);
	}
}
