<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Database\Factories;

use App\Models\UserGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserGroupFactory extends Factory
{
	protected $model = UserGroup::class;

	public function definition()
	{
		return [
			'name' => $this->faker->unique()->word(),
			'description' => $this->faker->sentence(),
		];
	}
}
