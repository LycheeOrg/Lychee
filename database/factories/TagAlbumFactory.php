<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Database\Factories;

use App\Models\Statistics;
use App\Models\Tag;
use App\Models\TagAlbum;
use Database\Factories\Traits\OwnedBy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TagAlbum>
 */
class TagAlbumFactory extends Factory
{
	use OwnedBy;

	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = TagAlbum::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return [
			'title' => 'Tag Album ' . fake()->year(),
			'owner_id' => 1,
		];
	}

	/**
	 * Define tags for that picture.
	 *
	 * @param array<int,Tag> $tags
	 *
	 * @return PhotoFactory
	 */
	public function of_tags(array $tags): self
	{
		return $this->afterCreating(function (TagAlbum $tag_album) use ($tags) {
			foreach ($tags as $tag) {
				if (!$tag instanceof Tag) {
					throw new \TypeError('Expected Tag instance, got ' . gettype($tag));
				}
				$tag_album->tags()->attach($tag);
			}
		});
	}

	/**
	 * Configure the model factory.
	 * We also create the associated statistics model.
	 */
	public function configure(): static
	{
		return $this->afterCreating(function (TagAlbum $album) {
			Statistics::factory()->with_album($album->id)->create();
			$album->fresh();
			$album->load('statistics');
		});
	}
}
