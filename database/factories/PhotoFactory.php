<?php

namespace Database\Factories;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Photo>
 */
class PhotoFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = Photo::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return [
			'title' => fake()->title(),
			'description' => null,
			'tags' => '',
			'is_public' => false,
			'owner_id' => 1,
			'type' => 'image/jpeg',
			'iso' => '100',
			'aperture' => 'f/2',
			'make' => 'Canon',
			'model' => 'Canon EOS R',
			'lens' => 'EF200mm f/2L IS',
			'shutter' => '1/320 s',
			'focal' => '200mm',
			'taken_at' => now(),
			'taken_at_orig_tz' => null,
			'is_starred' => false,
			'album_id' => null,
			'checksum' => sha1(rand()),
			'original_checksum' => sha1(rand()),
			'license' => 'none',
			'created_at' => now(),
			'updated_at' => now(),
		];
	}
}
