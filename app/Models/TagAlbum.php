<?php

namespace App\Models;

use App\Contracts\BaseAlbum;
use App\Exceptions\InvalidPropertyException;
use App\Models\Extensions\ForwardsToParentImplementation;
use App\Models\Extensions\HasBidirectionalRelationships;
use App\Models\Extensions\TagAlbumBuilder;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\Thumb;
use App\Relations\HasManyPhotosByTag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class TagAlbum.
 *
 * @property string show_tags
 */
class TagAlbum extends Model implements BaseAlbum
{
	use HasBidirectionalRelationships;
	use ForwardsToParentImplementation, ThrowsConsistentExceptions {
		ForwardsToParentImplementation::delete insteadof ThrowsConsistentExceptions;
		ForwardsToParentImplementation::delete as private parentDelete;
	}

	const FRIENDLY_MODEL_NAME = 'tag album';

	/**
	 * Indicates if the model's primary key is auto-incrementing.
	 *
	 * @var bool
	 */
	public $incrementing = false;

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

	/**
	 * Returns the relationship between this model and the implementation
	 * of the "parent" class.
	 *
	 * @return BelongsTo
	 */
	public function base_class(): BelongsTo
	{
		return $this->belongsTo(BaseAlbumImpl::class, 'id', 'id');
	}

	public function photos(): HasManyPhotosByTag
	{
		return new HasManyPhotosByTag($this);
	}

	/**
	 * @throws InvalidPropertyException
	 */
	protected function getThumbAttribute(): ?Thumb
	{
		// Note, `photos()` already applies a "security filter" and
		// only returns photos which are accessible by the current
		// user
		return Thumb::createFromPhotoRelation(
			$this->photos(), $this->sorting_col, $this->sorting_order
		);
	}

	/**
	 * Returns the relationship between an album and its owner.
	 *
	 * @return BelongsTo
	 */
	public function owner(): BelongsTo
	{
		return $this->base_class->owner();
	}

	/**
	 * Returns the relationship between an album and all users which whom
	 * this album is shared.
	 *
	 * @return BelongsToMany
	 */
	public function shared_with(): BelongsToMany
	{
		return $this->base_class->shared_with();
	}

	public function toArray(): array
	{
		$result = parent::toArray();
		$result['is_tag_album'] = true;

		return array_merge($result, $this->base_class->toArray());
	}

	/**
	 * {@inheritdoc}
	 */
	public function newEloquentBuilder($query): TagAlbumBuilder
	{
		return new TagAlbumBuilder($query);
	}

	protected function friendlyModelName(): string
	{
		return self::FRIENDLY_MODEL_NAME;
	}
}
