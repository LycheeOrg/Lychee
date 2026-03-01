<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Database\Factories;

use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContactMessage>
 */
class ContactMessageFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var class-string<ContactMessage>
	 */
	protected $model = ContactMessage::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return [
			'name' => $this->faker->name(),
			'email' => $this->faker->email(),
			'message' => $this->faker->paragraphs(2, true),
			'is_read' => false,
			'ip_address' => $this->faker->ipv4(),
			'user_agent' => $this->faker->userAgent(),
		];
	}

	public function read(): Factory
	{
		return $this->state(fn (array $attributes) => ['is_read' => true]);
	}
}
