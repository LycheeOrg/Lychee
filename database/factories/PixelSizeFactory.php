<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Database\Factories;

use App\Models\PixelSize;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PixelSize>
 */
class PixelSizeFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var class-string<PixelSize>
	 */
	protected $model = PixelSize::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array<string,mixed>
	 */
	public function definition(): array
	{
		$width = fake()->numberBetween(800, 6000);
		$height = fake()->numberBetween(600, 4000);

		return [
			'label' => "{$width}×{$height} px",
			'width' => $width,
			'height' => $height,
			'is_active' => true,
		];
	}

	/**
	 * Mark the pixel size as inactive.
	 *
	 * @return self
	 */
	public function inactive(): self
	{
		return $this->state(fn (array $attributes) => ['is_active' => false]);
	}
}
