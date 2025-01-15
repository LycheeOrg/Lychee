<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Database\Factories;

use App\Models\Album;
use Database\Factories\Traits\OwnedBy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Album>
 */
class AlbumFactory extends Factory
{
	use OwnedBy;

	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = Album::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return [
			'title' => fake()->country() . ' ' . fake()->year(),
			'owner_id' => 1,
		];
	}

	/**
	 * Defines the parent of the create album.
	 *
	 * @param Album $parent
	 *
	 * @return self
	 */
	public function children_of(Album $parent): Factory
	{
		return $this->afterMaking(
			fn (Album $album) => $parent->appendNode($album)
		)
			->afterCreating(function (Album $album) use ($parent) {
				$parent->load('children');
				$parent->fixOwnershipOfChildren();
			});
	}

	/**
	 * Make the album root.
	 *
	 * @return self
	 */
	public function as_root(): self
	{
		return $this->afterMaking(function (Album $album) {
			$album->makeRoot();
		});
	}
}
