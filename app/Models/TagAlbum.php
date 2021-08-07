<?php

namespace App\Models;

use App\Contracts\BaseModelAlbum;
use App\Models\Extensions\ForwardsToParentImplementation;
use App\Models\Extensions\HasBidirectionalRelationships;
use App\Models\Extensions\Thumb;
use App\Relations\HasManyPhotosByTag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

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
	 * @return MorphOne
	 */
	public function base_class(): MorphOne
	{
		return $this->morphOne(
			BaseModelAlbumImpl::class,
			BaseModelAlbumImpl::INHERITANCE_RELATION_NAME,
			BaseModelAlbumImpl::INHERITANCE_DISCRIMINATOR_COL_NAME,
			BaseModelAlbumImpl::INHERITANCE_ID_COL_NAME,
			BaseModelAlbumImpl::INHERITANCE_ID_COL_NAME
		);
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

	public function toArray(): array
	{
		$result = parent::toArray();
		$result['tag_album'] = true;

		return array_merge($result, $this->base_class->toArray());
	}
}
