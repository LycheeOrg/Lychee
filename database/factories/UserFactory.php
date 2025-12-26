<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Database\Factories;

use App\Enum\UserGroupRole;
use App\Models\User;
use App\Models\UserGroup;
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
	 * @var class-string<User>
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
			'username' => $this->faker->unique()->name(),
			'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
			'may_administrate' => false,
			'may_upload' => false,
			'email' => $this->faker->unique()->email(),
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
	 * Assign the user to a given group with a specific role when creating.
	 *
	 * @param UserGroup     $group
	 * @param UserGroupRole $role
	 *
	 * @return Factory
	 */
	public function with_group(UserGroup $group, UserGroupRole $role = UserGroupRole::MEMBER): Factory
	{
		return $this->afterCreating(function (User $user) use ($group, $role) {
			$user->user_groups()->attach($group, ['role' => $role]);
		});
	}
}
