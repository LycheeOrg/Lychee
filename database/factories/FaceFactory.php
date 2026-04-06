<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Database\Factories;

use App\Models\Face;
use App\Models\Person;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Face>
 */
class FaceFactory extends Factory
{
	protected $model = Face::class;

	/**
	 * @return array<string,mixed>
	 */
	public function definition(): array
	{
		return [
			'photo_id' => Photo::factory(),
			'person_id' => null,
			'x' => fake()->randomFloat(4, 0.0, 0.8),
			'y' => fake()->randomFloat(4, 0.0, 0.8),
			'width' => fake()->randomFloat(4, 0.05, 0.2),
			'height' => fake()->randomFloat(4, 0.05, 0.2),
			'confidence' => fake()->randomFloat(4, 0.5, 1.0),
			'crop_token' => Str::random(24),
			'is_dismissed' => false,
			'cluster_label' => null,
		];
	}

	public function for_photo(Photo $photo): self
	{
		return $this->state(fn () => ['photo_id' => $photo->id]);
	}

	public function for_person(Person $person): self
	{
		return $this->state(fn () => ['person_id' => $person->id]);
	}

	public function dismissed(): self
	{
		return $this->state(fn () => ['is_dismissed' => true]);
	}

	public function without_crop(): self
	{
		return $this->state(fn () => ['crop_token' => null]);
	}

	public function with_cluster(int $label): self
	{
		return $this->state(fn () => ['cluster_label' => $label]);
	}

	public function with_confidence(float $confidence): self
	{
		return $this->state(fn () => ['confidence' => $confidence]);
	}
}
