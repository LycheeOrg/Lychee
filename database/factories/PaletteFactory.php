<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Database\Factories;

use App\Models\Palette;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Palette>
 */
class PaletteFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = Palette::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return [
			'photo_id' => null,
			'colour_1' => 0,
			'colour_2' => 0,
			'colour_3' => 0,
			'colour_4' => 0,
			'colour_5' => 0,
		];
	}

	public function with_colour_1(int $colour): self
	{
		return $this->state(function (array $attributes) use ($colour) {
			return [
				'colour_1' => $colour,
			];
		});
	}

	public function with_colour_2(int $colour): self
	{
		return $this->state(function (array $attributes) use ($colour) {
			return [
				'colour_2' => $colour,
			];
		});
	}

	public function with_colour_3(int $colour): self
	{
		return $this->state(function (array $attributes) use ($colour) {
			return [
				'colour_3' => $colour,
			];
		});
	}

	public function with_colour_4(int $colour): self
	{
		return $this->state(function (array $attributes) use ($colour) {
			return [
				'colour_4' => $colour,
			];
		});
	}

	public function with_colour_5(int $colour): self
	{
		return $this->state(function (array $attributes) use ($colour) {
			return [
				'colour_5' => $colour,
			];
		});
	}

	public function with_photo(Photo $photo): self
	{
		return $this->state(function (array $attributes) use ($photo) {
			return [
				'photo_id' => $photo->id,
			];
		});
	}
}
