<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Database\Factories;

use App\Models\TagAlbum;
use Database\Factories\Traits\OwnedBy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TagAlbum>
 */
class TagAlbumFactory extends Factory
{
	use OwnedBy;

	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = TagAlbum::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return [
			'title' => 'Tag Album ' . fake()->year(),
			'owner_id' => 1,
		];
	}

	public function of_tags(string $tags): self
	{
		return $this->state(function (array $attributes) use ($tags) {
			return [
				'show_tags' => $tags,
			];
		});
	}
}
