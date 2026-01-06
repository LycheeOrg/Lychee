<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Database\Factories;

use App\Models\UserGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserGroupFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var class-string<UserGroup>
	 */
	protected $model = UserGroup::class;

	public function definition()
	{
		return [
			'name' => $this->faker->unique()->word(),
			'description' => $this->faker->sentence(),
		];
	}
}
