<?php

namespace App\Models;

use App\Models\Extensions\BaseAlbum;
use App\Models\Extensions\ForwardsToParentImplementation;
use App\Models\Extensions\TagAlbumBuilder;
use App\Models\Extensions\Thumb;
use App\Relations\HasManyPhotosByTag;

/**
 * Class TagAlbum.
 *
 * @property string show_tags
 */
class TagAlbum extends BaseAlbum
{
	use ForwardsToParentImplementation;

	/**
	 * The model's attributes.
	 *
	 * We must list all attributes explicitly here, otherwise the attributes
	 * of a new model will accidentally be set on the parent class.
	 * The trait {@link \App\Models\Extensions\ForwardsToParentImplementation}
	 * only works properly, if it knows which attributes belong to the parent
	 * class and which attributes belong to the child class.
	 *
	 * @var array
	 */
	protected $attributes = [
		'id' => null,
		'show_tags' => null,
	];

	protected $casts = [
		'min_taken_at' => 'datetime',
		'max_taken_at' => 'datetime',
	];

	/**
	 * @var string[] The list of attributes which exist as columns of the DB
	 *               relation but shall not be serialized to JSON
	 */
	protected $hidden = [
		'base_class', // don't serialize base class as a relation, the attributes of the base class are flatly merged into the JSON result
	];

	/**
	 * @var string[] The list of "virtual" attributes which do not exist as
	 *               columns of the DB relation but which shall be appended to
	 *               JSON from accessors
	 */
	protected $appends = [
		'thumb',
	];

	public function photos(): HasManyPhotosByTag
	{
		return new HasManyPhotosByTag($this);
	}

	protected function getThumbAttribute(): ?Thumb
	{
		// Note, `photos()` already applies a "security filter" and
		// only returns photos which are accessible by the current
		// user

		// TODO: Convert to proper relation

		/** @var Photo|null $cover */
		$cover = $this->photos()
			->without(['album'])
			->orderBy('photos.is_starred', 'DESC')
			->orderBy('photos.' . $this->sorting_col, $this->sorting_order)
			->select(['photos.id', 'photos.type'])
			->first();

		return Thumb::createFromPhoto($cover);
	}

	public function toArray(): array
	{
		$result = parent::toArray();
		$result['is_tag_album'] = true;

		return $result;
	}

	/**
	 * {@inheritdoc}
	 */
	public function newEloquentBuilder($query): TagAlbumBuilder
	{
		return new TagAlbumBuilder($query);
	}
}
