<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Database\Factories;

use App\Models\Album;
use App\Models\Palette;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Models\Statistics;
use App\Models\Tag;
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
	 * @var class-string<Photo>
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
			'is_highlighted' => false,
			'checksum' => sha1(rand()),
			'original_checksum' => sha1(rand()),
			'license' => 'none',
			'rating_avg' => null,
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

	public function with_palette(): Factory
	{
		return $this->afterCreating(function (Photo $photo) {
			Palette::factory()->with_colour_1(0xFF0000)
				->with_colour_2(0x00FF00)
				->with_colour_3(0x0000FF)
				->with_colour_4(0xFFFF00)
				->with_colour_5(0xFF00FF)
				->create(['photo_id' => $photo->id]);
			$photo->fresh();
			$photo->load('palette');
		});

		return $this;
	}

	/**
	 * Set a bunch of GPS coordinates (in Netherlands).
	 *
	 * @return PhotoFactory
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
	 * @return PhotoFactory
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

	/**
	 * Define tags for that picture.
	 *
	 * @param array<int,Tag> $tags
	 *
	 * @return PhotoFactory
	 */
	public function with_tags(array $tags): self
	{
		return $this->afterCreating(function (Photo $photo) use ($tags) {
			foreach ($tags as $tag) {
				if (!$tag instanceof Tag) {
					throw new \TypeError('Expected Tag instance, got ' . gettype($tag));
				}
				$photo->tags()->attach($tag);
			}
		});
	}

	/**
	 * Set a bunch of GPS coordinates (in Netherlands).
	 *
	 * @return self
	 */
	public function in(Album $album): self
	{
		return $this->hasAttached([$album])->afterCreating(function (Photo $photo) {
			$photo->load('albums', 'owner');
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
	 * We create 7 random Size Variants and the associated statistics model.
	 */
	public function configure(): self
	{
		return $this->afterCreating(function (Photo $photo) {
			Statistics::factory()->with_photo($photo->id)->create();
			$photo->fresh();

			// Creates the size variants
			if ($this->with_size_variants) {
				SizeVariant::factory()->count(7)->allSizeVariants()->create(['photo_id' => $photo->id]);
				$photo->fresh();
				$photo->load('size_variants');
			}

			$photo->load('palette');
			$photo->load('tags');
			$photo->load('statistics');

			// Reset the value if it was disabled.
			$this->with_size_variants = true;
		});
	}
}
