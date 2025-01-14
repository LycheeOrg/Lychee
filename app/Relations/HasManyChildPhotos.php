<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Relations;

use App\Contracts\Exceptions\InternalLycheeException;
use App\Eloquent\FixedQueryBuilder;
use App\Enum\OrderSortingType;
use App\Exceptions\Internal\InvalidOrderDirectionException;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use App\Models\Photo;
use App\Policies\PhotoQueryPolicy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\InvalidCastException;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends HasManyBidirectionally<Photo,Album>
 */
class HasManyChildPhotos extends HasManyBidirectionally
{
	protected PhotoQueryPolicy $photoQueryPolicy;

	public function __construct(Album $owningAlbum)
	{
		// Sic! We must initialize attributes of this class before we call
		// the parent constructor.
		// The parent constructor calls `addConstraints` and thus our own
		// attributes must be initialized by then
		$this->photoQueryPolicy = resolve(PhotoQueryPolicy::class);
		parent::__construct(
			Photo::query(),
			$owningAlbum,
			'album_id',
			'id',
			'album'
		);
	}

	/**
	 * @return FixedQueryBuilder<Photo>
	 */
	protected function getRelationQuery(): FixedQueryBuilder
	{
		/**
		 * We know that the internal query is of type `FixedQueryBuilder`,
		 * because it was set in the constructor as `Photo::query()`.
		 *
		 * @noinspection PhpIncompatibleReturnTypeInspection
		 *
		 * @phpstan-ignore-next-line
		 */
		return $this->query;
	}

	public function getParent(): Album
	{
		/**
		 * We know that the internal query is of type `Album`,
		 * because it was set in the constructor as `$owningAlbum`.
		 *
		 * @noinspection PhpIncompatibleReturnTypeInspection
		 */
		return $this->parent;
	}

	/**
	 * @throws InternalLycheeException
	 */
	public function addConstraints()
	{
		if (static::$constraints) {
			parent::addConstraints();
			$this->photoQueryPolicy->applyVisibilityFilter($this->getRelationQuery());
		}
	}

	/**
	 * @param Album[] $models
	 *
	 * @throws InternalLycheeException
	 */
	public function addEagerConstraints(array $models)
	{
		parent::addEagerConstraints($models);
		$this->photoQueryPolicy->applyVisibilityFilter($this->getRelationQuery());
	}

	/**
	 * @return Collection<int,Photo>
	 *
	 * @throws InvalidOrderDirectionException
	 */
	public function getResults(): Collection
	{
		if (is_null($this->getParentKey())) {
			return $this->related->newCollection();
		}

		$albumSorting = $this->getParent()->getEffectivePhotoSorting();

		/** @var SortingDecorator<Photo> */
		$sortingDecorator = new SortingDecorator($this->query);

		return $sortingDecorator
			->orderPhotosBy(
				$albumSorting->column,
				$albumSorting->order
			)
			->get();
	}

	/**
	 * Match the eagerly loaded results to their parents.
	 *
	 * @param Album[]               $models   an array of parent models
	 * @param Collection<int,Photo> $results  the unified collection of all child models of all parent models
	 * @param string                $relation the name of the relation from the parent to the child models
	 *
	 * @return array<int,Album>
	 *
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
				/** @var Collection<int,Photo> $childrenOfModel */
				$childrenOfModel = $this->getRelationValue($dictionary, $key, 'many');
				$sorting = $model->getEffectivePhotoSorting();
				$childrenOfModel = $childrenOfModel
					->sortBy(
						$sorting->column->value,
						in_array($sorting->column, SortingDecorator::POSTPONE_COLUMNS, true) ? SORT_NATURAL | SORT_FLAG_CASE : SORT_REGULAR,
						$sorting->order === OrderSortingType::DESC
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
