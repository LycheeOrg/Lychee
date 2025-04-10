<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Database\Factories;

use App\Enum\SizeVariantType;
use App\Models\Statistics;
use App\Models\TagAlbum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Statistics>
 */
class StatisticFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = Statistics::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return [
            'album_id' => null,
            'photo_id' => null,
            'visit_count' => 0,
            'download_count' => 0,
            'favourite_count' => 0,
            'shared_count' => 0,
        ];
	}

	public function with_album(string $album_id): self
	{
		return $this->state(function (array $attributes) use ($album_id) {
			return [
				'album_id' => $album_id,
			];
		});
	}

	public function with_photo(string $photo_id): self
	{
		return $this->state(function (array $attributes) use ($photo_id) {
			return [
				'photo_id' => $photo_id,
			];
		});
	}

	/**
	 * Configure the model factory.
	 * We also create the associated statistics model.
	 */
	public function configure(): static
	{
		return $this->afterCreating(function (TagAlbum $album) {
			Statistics::factory()->with_album($album->id)->create();
			$album->fresh();
			$album->load('statistics');
		});
	}

}
