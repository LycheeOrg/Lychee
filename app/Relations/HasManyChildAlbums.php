<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Relations;

use App\Contracts\Exceptions\InternalLycheeException;
use App\Enum\OrderSortingType;
use App\Exceptions\Internal\InvalidOrderDirectionException;
use App\Models\Album;
use App\Models\Builders\AlbumBuilder;
use App\Models\Extensions\SortingDecorator;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends HasManyBidirectionally<Album,Album>
 */
class HasManyChildAlbums extends HasManyBidirectionally
{
	protected AlbumQueryPolicy $album_query_policy;

	public function __construct(Album $owning_album)
	{
		// Sic! We must initialize attributes of this class before we call
		// the parent constructor.
		// The parent constructor calls `addConstraints` and thus our own
		// attributes must be initialized by then
		$this->album_query_policy = resolve(AlbumQueryPolicy::class);

		parent::__construct(
			$owning_album->newQuery(),
			$owning_album,
			'parent_id',
			'id',
			'parent'
		);
	}

	protected function getRelationQuery(): AlbumBuilder
	{
		/**
		 * We know that the internal query is of type `AlbumBuilder`,
		 * because it was set in the constructor as `$owningAlbum->newQuery()`.
		 *
		 * @noinspection PhpIncompatibleReturnTypeInspection
		 */
		return $this->query;
	}

	/**
	 * @throws InternalLycheeException
	 */
	public function addConstraints()
	{
		if (static::$constraints) {
			parent::addConstraints();
			$this->album_query_policy->applyVisibilityFilter($this->getRelationQuery());
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
		$this->album_query_policy->applyVisibilityFilter($this->getRelationQuery());
	}

	/**
	 * @return Collection<int,Album>
	 *
	 * @throws InvalidOrderDirectionException
	 */
	public function getResults(): Collection
	{
		if (is_null($this->getParentKey())) {
			return $this->related->newCollection();
		}

		$album_sorting = $this->getParent()->getEffectiveAlbumSorting();

		/** @var SortingDecorator<Album> */
		$sorting_decorator = new SortingDecorator($this->query);

		return $sorting_decorator
			->orderBy(
				$album_sorting->column,
				$album_sorting->order)
			->get();
	}

	/**
	 * Match the eagerly loaded results to their parents.
	 *
	 * @param Album[]               $models   an array of parent models
	 * @param Collection<int,Album> $results  the unified collection of all child models of all parent models
	 * @param string                $relation the name of the relation from the parent to the child models
	 *
	 * @return array<int,Album>
	 */
	public function match(array $models, Collection $results, $relation): array
	{
		$dictionary = $this->buildDictionary($results);

		// Once we have the dictionary we can simply spin through the parent models to
		// link them up with their children using the keyed dictionary to make the
		// matching very convenient and easy work. Then we'll just return them.
		foreach ($models as $model) {
			if (isset($dictionary[$key = $this->getDictionaryKey($model->getAttribute($this->localKey))])) {
				/** @var Collection<int,Album> $children_of_model */
				$children_of_model = $this->getRelationValue($dictionary, $key, 'many');
				$sorting = $model->getEffectiveAlbumSorting();
				$children_of_model = $children_of_model
					->sortBy($sorting->column->value, SORT_NATURAL | SORT_FLAG_CASE, $sorting->order === OrderSortingType::DESC)
					->values();
				$model->setRelation($relation, $children_of_model);
				// This is the newly added code which sets this method apart
				// from the original method and additionally sets the
				// reverse link
				foreach ($children_of_model as $child_model) {
					$child_model->setRelation($this->foreign_method_name, $model);
				}
			}
		}

		return $models;
	}
}