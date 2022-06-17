<?php

namespace App\Relations;

use App\Actions\PhotoAuthorisationProvider;
use App\Contracts\InternalLycheeException;
use App\DTO\SortingCriterion;
use App\Exceptions\Internal\InvalidOrderDirectionException;
use App\Models\Album;
use App\Models\Extensions\FixedQueryBuilder;
use App\Models\Extensions\SortingDecorator;
use App\Models\Photo;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\InvalidCastException;
use Illuminate\Database\Eloquent\Model;

class HasManyChildPhotos extends HasManyBidirectionally
{
	protected PhotoAuthorisationProvider $photoAuthorisationProvider;

	public function __construct(Album $owningAlbum)
	{
		// Sic! We must initialize attributes of this class before we call
		// the parent constructor.
		// The parent constructor calls `addConstraints` and thus our own
		// attributes must be initialized by then
		$this->photoAuthorisationProvider = resolve(PhotoAuthorisationProvider::class);
		parent::__construct(
			Photo::query(),
			$owningAlbum,
			'album_id',
			'id',
			'album'
		);
	}

	protected function getRelationQuery(): FixedQueryBuilder
	{
		/*
		 * We know that the internal query is of type `FixedQueryBuilder`,
		 * because it was set int the constructor as `Photo::query()`.
		 */
		return $this->query; // @phpstan-ignore-line @noinspection PhpIncompatibleReturnTypeInspection
	}

	public function getParent(): Album
	{
		/*
		 * We know that the internal query is of type `Album`,
		 * because it was set int the constructor as `$owningAlbum`.
		 */
		return $this->parent; // @phpstan-ignore-line @noinspection PhpIncompatibleReturnTypeInspection
	}

	/**
	 * @throws InternalLycheeException
	 */
	public function addConstraints()
	{
		if (static::$constraints) {
			parent::addConstraints();
			$this->photoAuthorisationProvider->applyVisibilityFilter($this->getRelationQuery());
		}
	}

	/**
	 * @throws InternalLycheeException
	 */
	public function addEagerConstraints(array $models)
	{
		parent::addEagerConstraints($models);
		$this->photoAuthorisationProvider->applyVisibilityFilter($this->getRelationQuery());
	}

	/**
	 * @throws InvalidOrderDirectionException
	 */
	public function getResults()
	{
		if (is_null($this->getParentKey())) {
			return $this->related->newCollection();
		}

		$albumSorting = $this->getParent()->getEffectiveSorting();

		return (new SortingDecorator($this->query))
			->orderBy(
				'photos.' . $albumSorting->column,
				$albumSorting->order
			)
			->get();
	}

	/**
	 * Match the eagerly loaded results to their parents.
	 *
	 * @param array      $models   an array of parent models
	 * @param Collection $results  the unified collection of all child models of all parent models
	 * @param string     $relation the name of the relation from the parent to the child models
	 *
	 * @return array
	 *
	 * @throws InvalidArgumentException
	 * @throws \LogicException
	 * @throws InvalidCastException
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
				$sorting = $model->getEffectiveSorting();
				$childrenOfModel = $childrenOfModel
					->sortBy(
						$sorting->column,
						in_array($sorting->column, SortingDecorator::POSTPONE_COLUMNS, true) ? SORT_NATURAL | SORT_FLAG_CASE : SORT_REGULAR,
						$sorting->order === SortingCriterion::DESC
					)
					->values();
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
