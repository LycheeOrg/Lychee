<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Database\Factories;

use App\Enum\RenamerModeType;
use App\Models\RenamerRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RenamerRule>
 */
class RenamerRuleFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var class-string<RenamerRule>
	 */
	protected $model = RenamerRule::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return [
			'order' => null,
			'owner_id' => null,
			'rule' => null,
			'description' => null,
			'needle' => null,
			'replacement' => null,
			'mode' => null,
			'is_enabled' => true,
			'is_photo_rule' => true,
			'is_album_rule' => true,
		];
	}

	public function order(int $order): self
	{
		return $this->state(function (array $attributes) use ($order) {
			return [
				'order' => $order,
			];
		});
	}

	public function owner_id(int $owner_id): self
	{
		return $this->state(function (array $attributes) use ($owner_id) {
			return [
				'owner_id' => $owner_id,
			];
		});
	}

	public function is_photo_rule(bool $is_photo_rule): self
	{
		return $this->state(function (array $attributes) use ($is_photo_rule) {
			return [
				'is_photo_rule' => $is_photo_rule,
			];
		});
	}

	public function is_album_rule(bool $is_album_rule): self
	{
		return $this->state(function (array $attributes) use ($is_album_rule) {
			return [
				'is_album_rule' => $is_album_rule,
			];
		});
	}

	public function rule(string $rule): self
	{
		return $this->state(function (array $attributes) use ($rule) {
			return [
				'rule' => $rule,
			];
		});
	}

	public function description(string $description): self
	{
		return $this->state(function (array $attributes) use ($description) {
			return [
				'description' => $description,
			];
		});
	}

	public function needle(string $needle): self
	{
		return $this->state(function (array $attributes) use ($needle) {
			return [
				'needle' => $needle,
			];
		});
	}

	public function replacement(string $replacement): self
	{
		return $this->state(function (array $attributes) use ($replacement) {
			return [
				'replacement' => $replacement,
			];
		});
	}

	public function mode(RenamerModeType $mode): self
	{
		return $this->state(function (array $attributes) use ($mode) {
			return [
				'mode' => $mode->value,
			];
		});
	}
}
