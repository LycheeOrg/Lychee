<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
	protected $name_generated = [];
	protected $email_generated = [];

	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = User::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array<string,mixed>
	 */
	public function definition(): array
	{
		return [
			'username' => $this->get_name(),
			'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
			'may_administrate' => false,
			'may_upload' => false,
			'email' => $this->get_email(),
			'token' => null,
			'remember_token' => null,
			'may_edit_own_settings' => true,
		];
	}

	/**
	 * Indicate that user is Admin.
	 *
	 * @return Factory
	 */
	public function may_administrate(): Factory
	{
		return $this->state(function (array $attributes) {
			return [
				'may_administrate' => true,
			];
		});
	}

	/**
	 * Indicate that the user has upload rights.
	 *
	 * @return Factory
	 */
	public function may_upload(): Factory
	{
		return $this->state(function (array $attributes) {
			return [
				'may_upload' => true,
			];
		});
	}

	/**
	 * Indicates the user is locked.
	 *
	 * @return Factory
	 */
	public function locked(): Factory
	{
		return $this->state(function (array $attributes) {
			return ['may_edit_own_settings' => false];
		});
	}

	/**
	 * Avoid collision of generated names.
	 *
	 * @return string
	 */
	private function get_name(): string
	{
		$candidate = fake()->name();
		$i = 5;
		while ($i > 0 && in_array($candidate, $this->name_generated, true)) {
			$candidate = fake()->name();
			$i--;
		}
		if ($i === 0) {
			throw new \TypeError('Could not generate unique name');
		}
		$this->name_generated[] = $candidate;

		return $candidate;
	}

	private function get_email(): string
	{
		$candidate = fake()->email();
		$i = 5;
		while ($i > 0 && in_array($candidate, $this->email_generated, true)) {
			$candidate = fake()->email();
			$i--;
		}
		if ($i === 0) {
			throw new \TypeError('Could not generate unique email');
		}
		$this->email_generated[] = $candidate;

		return $candidate;
	}
}
