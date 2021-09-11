<?php

namespace App\Relations;

use App\Actions\PhotoAuthorisationProvider;
use App\Contracts\AbstractAlbum;
use App\Contracts\BaseAlbum;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Common base class of all photo relations for albums which are not the
 * direct parent of the queried photos, but include the photo due to some
 * indirect condition.
 */
abstract class HasManyPhotos extends Relation
{
	protected PhotoAuthorisationProvider $photoAuthorisationProvider;
	protected AbstractAlbum $owningAlbum;

	public function __construct(AbstractAlbum $owningAlbum)
	{
		// Sic! We must initialize attributes of this class before we call
		// the parent constructor.
		// The parent constructor calls `addConstraints` and thus our own
		// attributes must be initialized by then
		$this->photoAuthorisationProvider = resolve(PhotoAuthorisationProvider::class);
		$this->owningAlbum = $owningAlbum;
		// This is a hack.
		// The abstract class
		// {@link \Illuminate\Database\Eloquent\Relations\Relation}
		// stores a pointer to the parent and assumes that the parent is
		// an instance of {@link Illuminate\Database\Eloquent\Model}.
		// However, we cannot guarantee this, because we have smart albums
		// which do not exist on the DB and therefore do not extend
		// `Model`.
		// Actually, it is sufficient if the owning side implements the
		// method which are provided by `HasRelations`.
		// Unfortunately, the constructor of `Relation` demands a true model
		// and does not only ask for something which implements `HasRelations`.
		// Luckily, `Relation` itself does not do anything with the passed
		// model but only stores a reference in `Relation::$parent` to be
		// used by child classes.
		// Moreover, it is impossible to pass `null`.
		// As a work-around we store the owning album in our own attribute
		// `$owningAlbum` and always use that instead of `$parent`.
		parent::__construct(
			// Sic! We also must load the album eagerly.
			// This relation is not used by albums which own the queried
			// photos, but by albums which only include the photos due to some
			// indirect condition.
			// Hence, the actually owning albums of the photos are not
			// necessarily loaded.
			Photo::query()->with(['album', 'size_variants_raw', 'size_variants_raw.sym_links']),
			new DummyModel()
		);
	}

	/**
	 * Initializes the given owning models with a default value of this
	 * relation.
	 *
	 * In this case, the default value is an empty collection of
	 * {@link \App\Models\Photo}.
	 *
	 * @param array  $models   a list of owning models, i.e. a list of albums
	 * @param string $relation the name of the relation on the owning models
	 *
	 * @return array always returns $models
	 */
	public function initRelation(array $models, $relation): array
	{
		/** @var AbstractAlbum $model */
		foreach ($models as $model) {
			$model->setRelation($relation, $this->related->newCollection());
		}

		return $models;
	}

	/**
	 * Returns the collection of photos for a single owning parent (aka
	 * "album").
	 *
	 * This method also takes care of proper sorting.
	 * For most columns this method performs sorting on the DB layer for
	 * improved performance.
	 * But for some columns which require "natural" and locale-dependent
	 * sorting, the collection is sorted after is has been fetched from
	 * the DB.
	 *
	 * @return Collection
	 */
	public function getResults(): Collection
	{
		if ($this->owningAlbum instanceof BaseAlbum) {
			$sortingCol = $this->owningAlbum->sorting_col;
			$sortingOrder = $this->owningAlbum->sorting_order;
		} else {
			$sortingCol = Configs::get_value('sorting_Photos_col');
			$sortingOrder = Configs::get_value('sorting_Photos_order');
		}

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
}
