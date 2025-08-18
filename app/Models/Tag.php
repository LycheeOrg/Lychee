<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models;

use App\Models\Builders\TagBuilder;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\ToArrayThrowsNotImplemented;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\Builder as BaseBuilder;

/**
 * App\Models\Tag.
 *
 * @property int         $id
 * @property string      $name
 * @property string|null $description
 */
class Tag extends Model
{
	/** @phpstan-use HasFactory<\Database\Factories\TagFactory> */
	use HasFactory;
	use ToArrayThrowsNotImplemented;
	use ThrowsConsistentExceptions {
		delete as parentDelete;
	}

	// Disable the default timestamps handling
	public $timestamps = false;

	/**
	 * @var list<string> the attributes that are mass assignable
	 */
	protected $fillable = [
		'name',
		'description',
	];

	protected $hidden = [];

	/**
	 * Create a new Eloquent query builder for the model.
	 *
	 * @param BaseBuilder $query
	 *
	 * @return TagBuilder
	 */
	public function newEloquentBuilder($query): TagBuilder
	{
		return new TagBuilder($query);
	}

	/**
	 * Returns the relationship between a tag and all photos with whom
	 * this tag is attached.
	 *
	 * @return BelongsToMany<Photo,$this>
	 */
	public function photos(): BelongsToMany
	{
		return $this->belongsToMany(
			Photo::class,
			'photos_tags',
			'tag_id',
			'photo_id',
		);
	}

	/**
	 * Fetches the tags from the database, creating them if they do not exist.
	 *
	 * @param array $tags
	 *
	 * @return Collection<int,self>
	 */
	public static function from(array $tags): Collection
	{
		// Trim whitespace from each tag
		$tags = array_map(fn ($tag) => ucwords(strtolower(trim($tag))), $tags);
		// Filter out empty tags
		$tags = array_filter($tags, fn ($tag) => $tag !== '');

		// Fetch existing tags
		$existing_tags = self::whereIn('name', $tags)->get();

		// figure out the missing ones and create them.
		$missing_tags = array_diff($tags, $existing_tags->pluck('name')->all());
		if (count($missing_tags) > 0) {
			// Create missing tags
			self::insert(array_map(fn ($name) => ['name' => $name], $missing_tags));
			$existing_tags = $existing_tags->merge(self::whereIn('name', $missing_tags)->get());
		}

		return $existing_tags;
	}
}
