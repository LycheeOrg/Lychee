<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Database\Factories;

use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccessPermission>
 */
class AccessPermissionFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = AccessPermission::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return
		[
			'is_link_required' => true,
			'grants_full_photo_access' => false,
			'grants_download' => false,
			'grants_upload' => false,
			'grants_edit' => false,
			'grants_delete' => false,
		];
	}

	public function public()
	{
		return $this->state(function (array $attributes) {
			return [
				'user_id' => null,
			];
		});
	}

	public function locked()
	{
		return $this->state(function (array $attributes) {
			return [
				'password' => Hash::make('password'),
			];
		});
	}

	public function for_user(User $user)
	{
		return $this->state(function (array $attributes) use ($user) {
			return [
				'user_id' => $user->id,
			];
		})->afterCreating(function (AccessPermission $perm) {
			$perm->load('album', 'user');
		});
	}

	public function grants_edit()
	{
		return $this->state(function (array $attributes) {
			return [
				'grants_edit' => true,
			];
		});
	}

	public function grants_delete()
	{
		return $this->state(function (array $attributes) {
			return [
				'grants_delete' => true,
			];
		});
	}

	public function grants_upload()
	{
		return $this->state(function (array $attributes) {
			return [
				'grants_upload' => true,
			];
		});
	}

	public function grants_download()
	{
		return $this->state(function (array $attributes) {
			return [
				'grants_download' => true,
			];
		});
	}

	public function grants_full_photo()
	{
		return $this->state(function (array $attributes) {
			return [
				'grants_full_photo_access' => true,
			];
		});
	}

	public function visible()
	{
		return $this->state(function (array $attributes) {
			return [
				'is_link_required' => false,
			];
		});
	}

	public function for_album(Album $album)
	{
		return $this->state(function (array $attributes) use ($album) {
			return [
				'base_album_id' => $album->id,
			];
		})->afterCreating(function (AccessPermission $perm) {
			$perm->load('album', 'user');
		});
	}
}