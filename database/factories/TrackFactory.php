<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Database\Factories;

use App\Enum\StorageDiskType;
use App\Models\Album;
use App\Models\Track;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Track>
 */
class TrackFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var class-string<Track>
	 */
	protected $model = Track::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		$name = fake()->word();

		return [
			'name' => $name,
			'file_name' => 'tracks/' . fake()->sha1() . '.gpx',
			'disk' => StorageDiskType::LOCAL,
			'is_primary' => false,
		];
	}

	/**
	 * Set the album for this track.
	 *
	 * @param Album $album
	 *
	 * @return self
	 */
	public function for_album(Album $album): self
	{
		return $this->state([
			'album_id' => $album->id,
		]);
	}

	/**
	 * Mark this track as the album's primary track.
	 *
	 * @return self
	 */
	public function primary(): self
	{
		return $this->state([
			'is_primary' => true,
		]);
	}
}
