<?php

namespace App\Models;

use App\Contracts\BaseModelAlbum;
use App\Models\Extensions\ForwardsToParentImplementation;
use App\Models\Extensions\HasBidirectionalRelationships;
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
class TagAlbum extends Model implements BaseModelAlbum
{
	use HasBidirectionalRelationships;
	use ForwardsToParentImplementation;

	/**
	 * Indicates if the model's primary key is auto-incrementing.
	 *
	 * @var bool
	 */
	public $incrementing = false;

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
		return $this->belongsTo(BaseModelAlbumImpl::class, 'id', 'id');
	}

	public function photos(): HasManyPhotosByTag
	{
		return new HasManyPhotosByTag($this);
	}

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
		$result['tag_album'] = true;

		return array_merge($result, $this->base_class->toArray());
	}
}
