<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Database\Factories;

use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person>
 */
class PersonFactory extends Factory
{
	protected $model = Person::class;

	/**
	 * @return array<string,mixed>
	 */
	public function definition(): array
	{
		return [
			'name' => fake()->name(),
			'user_id' => null,
			'is_searchable' => true,
			'representative_face_id' => null,
		];
	}

	public function not_searchable(): self
	{
		return $this->state(fn () => ['is_searchable' => false]);
	}

	public function linked_to(User $user): self
	{
		return $this->state(fn () => ['user_id' => $user->id]);
	}

	public function with_name(string $name): self
	{
		return $this->state(fn () => ['name' => $name]);
	}
}
