<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Database\Factories;

use App\Enum\SizeVariantType;
use App\Models\SizeVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SizeVariant>
 */
class SizeVariantFactory extends Factory
{
	private const H = 360;
	private const W = 540;
	private const FS = 141011;

	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = SizeVariant::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		$hash = fake()->sha1();
		$url = substr($hash, 0, 2) . '/' . substr($hash, 2, 2) . '/' . substr($hash, 4) . '.jpg';

		return ['type' => SizeVariantType::ORIGINAL, 'short_path' => SizeVariantType::ORIGINAL->name() . '/' . $url, 'ratio' => 1.5, 'height' => self::H * 8, 'width' => self::W * 8, 'filesize' => 64 * self::FS, 'storage_disk' => 'images'];
	}

	/**
	 * Creates 7 size variant with correct type and size,.
	 */
	public function allSizeVariants(): Factory
	{
		$hash = fake()->sha1();
		$url = substr($hash, 0, 2) . '/' . substr($hash, 2, 2) . '/' . substr($hash, 4) . '.jpg';

		return $this->state(new Sequence(
			['type' => SizeVariantType::ORIGINAL, 'short_path' => SizeVariantType::ORIGINAL->name() . '/' . $url, 'ratio' => 1.5, 'height' => self::H * 8, 'width' => self::W * 8, 'filesize' => 64 * self::FS, 'storage_disk' => 'images'],
			['type' => SizeVariantType::MEDIUM2X, 'short_path' => SizeVariantType::MEDIUM2X->name() . '/' . $url, 'ratio' => 1.5, 'height' => self::H * 6, 'width' => self::W * 6, 'filesize' => 36 * self::FS, 'storage_disk' => 'images'],
			['type' => SizeVariantType::MEDIUM, 'short_path' => SizeVariantType::MEDIUM->name() . '/' . $url, 'ratio' => 1.5, 'height' => self::H * 3, 'width' => self::W * 3, 'filesize' => 9 * self::FS, 'storage_disk' => 'images'],
			['type' => SizeVariantType::SMALL2X, 'short_path' => SizeVariantType::SMALL2X->name() . '/' . $url, 'ratio' => 1.5, 'height' => self::H * 2, 'width' => self::W * 2, 'filesize' => 4 * self::FS, 'storage_disk' => 'images'],
			['type' => SizeVariantType::SMALL, 'short_path' => SizeVariantType::SMALL->name() . '/' . $url, 'ratio' => 1.5, 'height' => self::H, 'width' => self::W, 'filesize' => self::FS, 'storage_disk' => 'images'],
			['type' => SizeVariantType::THUMB2X, 'short_path' => SizeVariantType::THUMB2X->name() . '/' . $url, 'ratio' => 1.5, 'height' => 400, 'width' => 400, 'filesize' => 160_000, 'storage_disk' => 'images'],
			['type' => SizeVariantType::THUMB, 'short_path' => SizeVariantType::THUMB->name() . '/' . $url, 'ratio' => 1.5, 'height' => 200, 'width' => 200, 'filesize' => 40_000, 'storage_disk' => 'images'],
		));
	}
}
