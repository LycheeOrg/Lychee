<?php

namespace App\Relations;

use App\Models\Photo;
use App\Models\UnTaggedAlbum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class HasManyUnTaggedPhotos extends BaseHasManyPhotos
{
	protected UnTaggedAlbum $album;

	public function __construct()
	{
		parent::__construct();

		$user_id = Auth::user()->id;
		$this->query->where('owner_id', '=', $user_id)
					->whereDoesntHave('tags');
	}

	/**
	 * Apply base constraints when the relation is first loaded.
	 */
	public function addConstraints(): void
	{
		if (static::$constraints) {
			$this->query->where('owner_id', '=', Auth::user()->id)
						->whereDoesntHave('tags');
		}
	}

	/**
	 * Add constraints for eager loading.
	 *
	 * @param array<int, \App\Models\BaseAlbum> $models
	 */
	public function addEagerConstraints(array $models): void
	{
		$owner_ids = Auth::user()->id;

		$this->query->whereIn('owner_id', $owner_ids)
					->whereDoesntHave('tags');
	}

	/**
	 * Match the eagerly loaded results to their parent models.
	 *
	 * @param array<int, \App\Models\BaseAlbum>                    $models
	 * @param \Illuminate\Database\Eloquent\Collection<int, Photo> $results
	 * @param string                                               $relation
	 *
	 * @return array<int, \App\Models\BaseAlbum>
	 */
	public function match(array $models, Collection $results, $relation): array
	{
		$grouped_results = $results->groupBy('owner_id');

		foreach ($models as $model) {
			if ($model instanceof UnTaggedAlbum) {
				$model->setRelation(
					$relation,
					$grouped_results->get(Auth::user()->id, collect())
				);
			}
		}

		return $models;
	}
}
