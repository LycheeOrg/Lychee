<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Database\Factories;

use App\Enum\LandingFeaturedItemType;
use App\Models\LandingFeaturedItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LandingFeaturedItem>
 */
class LandingFeaturedItemFactory extends Factory
{
	protected $model = LandingFeaturedItem::class;

	public function definition(): array
	{
		return [
			'item_type' => LandingFeaturedItemType::ALBUM,
			'item_id' => (string) Str::ulid(),
			'sort_order' => 0,
			'enabled' => true,
		];
	}

	public function disabled(): static
	{
		return $this->state(fn (array $attributes) => ['enabled' => false]);
	}

	public function photo(string $photo_id): static
	{
		return $this->state(fn (array $attributes) => ['item_type' => LandingFeaturedItemType::PHOTO, 'item_id' => $photo_id]);
	}

	public function album(string $album_id): static
	{
		return $this->state(fn (array $attributes) => ['item_type' => LandingFeaturedItemType::ALBUM, 'item_id' => $album_id]);
	}
}
