<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Database\Factories;

use App\Models\PrintSize;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PrintSize>
 */
class PrintSizeFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var class-string<PrintSize>
	 */
	protected $model = PrintSize::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array<string,mixed>
	 */
	public function definition(): array
	{
		$width = fake()->numberBetween(10, 100);
		$height = fake()->numberBetween(10, 100);
		$unit = fake()->randomElement(['cm', 'inch']);

		return [
			'label' => "{$width}×{$height} {$unit}",
			'width' => $width,
			'height' => $height,
			'unit' => $unit,
			'paper_type' => fake()->optional()->randomElement(['Glossy', 'Matte', 'Silk', 'Canvas']),
			'is_active' => true,
		];
	}

	/**
	 * Mark the print size as inactive.
	 *
	 * @return self
	 */
	public function inactive(): self
	{
		return $this->state(fn (array $attributes) => ['is_active' => false]);
	}
}
