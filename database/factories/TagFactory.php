<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

class TagFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var class-string<Tag>
	 */
	protected $model = Tag::class;

	public function definition()
	{
		return [
			'name' => $this->faker->unique()->word(),
			'description' => $this->faker->sentence(),
		];
	}

	/**
	 * define name for that tag.
	 *
	 * @return self
	 */
	public function with_name(string $name): self
	{
		return $this->state(function (array $attributes) use ($name) {
			return [
				'name' => $name,
			];
		});
	}
}
