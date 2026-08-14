<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Database\Factories;

use App\Enum\LandingLinkPlacement;
use App\Models\LandingLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LandingLink>
 */
class LandingLinkFactory extends Factory
{
	protected $model = LandingLink::class;

	public function definition(): array
	{
		return [
			'label' => $this->faker->words(2, true),
			'url' => $this->faker->url(),
			'placement' => $this->faker->randomElement(LandingLinkPlacement::cases()),
			'open_in_new_tab' => true,
			'sort_order' => 0,
			'enabled' => true,
		];
	}

	public function disabled(): static
	{
		return $this->state(fn (array $attributes) => ['enabled' => false]);
	}

	public function nav(): static
	{
		return $this->state(fn (array $attributes) => ['placement' => LandingLinkPlacement::NAV]);
	}

	public function footer(): static
	{
		return $this->state(fn (array $attributes) => ['placement' => LandingLinkPlacement::FOOTER]);
	}

	public function both(): static
	{
		return $this->state(fn (array $attributes) => ['placement' => LandingLinkPlacement::BOTH]);
	}

	public function builtIn(): static
	{
		return $this->state(fn (array $attributes) => ['is_built_in' => true]);
	}
}
