<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Database\Factories;

use App\Models\Album;
use App\Models\Photo;
use App\Models\SizeVariant;
use Database\Factories\Traits\OwnedBy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Photo>
 */
class PhotoFactory extends Factory
{
	use OwnedBy;

	private bool $with_size_variants = true;

	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = Photo::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array<string,mixed>
	 */
	public function definition(): array
	{
		$now = now();

		return [
			'title' => 'CR_' . fake()->numerify('####'),
			'description' => null,
			'tags' => '',
			'owner_id' => 1,
			'type' => 'image/jpeg',
			'iso' => '100',
			'aperture' => 'f/2',
			'make' => 'Canon',
			'model' => 'Canon EOS R',
			'lens' => 'EF200mm f/2L IS',
			'shutter' => '1/320 s',
			'focal' => '200mm',
			'initial_taken_at' => $now,
			'taken_at_orig_tz' => null,
			'taken_at' => $now,
			'initial_taken_at_orig_tz' => null,
			'is_starred' => false,
			'album_id' => null,
			'checksum' => sha1(rand()),
			'original_checksum' => sha1(rand()),
			'license' => 'none',
			'created_at' => now(),
			'updated_at' => now(),
		];
	}

	/**
	 * Indicate that the user is suspended.
	 */
	public function without_size_variants(): Factory
	{
		$this->with_size_variants = false;

		return $this;
	}

	/**
	 * Set a bunch of GPS coordinates (in Netherlands).
	 *
	 * @return array<string,mixed>
	 */
	public function with_GPS_coordinates(): self
	{
		return $this->state(function (array $attributes) {
			return [
				'latitude' => '51.81738000',
				'longitude' => '5.86694306',
				'altitude' => '83.1000',
			];
		});
	}

	/**
	 * Set a bunch of GPS coordinates (in Netherlands).
	 *
	 * @return array<string,mixed>
	 */
	public function with_subGPS_coordinates(): self
	{
		return $this->state(function (array $attributes) {
			return [
				'latitude' => '-51.81738000',
				'longitude' => '-5.86694306',
				'altitude' => '83.1000',
			];
		});
	}

	/** define tags for that picture */
	public function with_tags(string $tags): self
	{
		return $this->state(function (array $attributes) use ($tags) {
			return [
				'tags' => $tags,
			];
		});
	}

	/**
	 * Set a bunch of GPS coordinates (in Netherlands).
	 *
	 * @return self
	 */
	public function in(Album $album): self
	{
		return $this->state(function (array $attributes) use ($album) {
			return [
				'album_id' => $album->id,
			];
		})->afterCreating(function (Photo $photo) {
			$photo->load('album', 'owner');
		});
	}

	/**
	 * define checksum for that picture.
	 *
	 * @return self
	 */
	public function with_checksum(string $checksum): self
	{
		return $this->state(function (array $attributes) use ($checksum) {
			return [
				'checksum' => $checksum,
				'original_checksum' => $checksum,
			];
		});
	}

	/**
	 * define title for that picture.
	 *
	 * @return self
	 */
	public function with_title(string $title): self
	{
		return $this->state(function (array $attributes) use ($title) {
			return [
				'title' => $title,
			];
		});
	}

	/**
	 * Configure the model factory.
	 * We create 7 random Size Variants.
	 */
	public function configure(): static
	{
		return $this->afterCreating(function (Photo $photo) {
			// Creates the size variants
			if ($this->with_size_variants) {
				SizeVariant::factory()->count(7)->allSizeVariants()->create(['photo_id' => $photo->id]);
				$photo->fresh();
				$photo->load('size_variants');
			}

			// Reset the value if it was disabled.
			$this->with_size_variants = true;
		});
	}
}
