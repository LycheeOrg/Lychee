<?php

namespace App\SmartAlbums;

use App\Casts\MustNotSetCast;
use App\Contracts\BaseAlbum;
use App\Models\Configs;
use App\Models\Extensions\Thumb;
use App\Models\Extensions\UTCBasedTimes;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;

/**
 * Class BaseSmartAlbum.
 *
 * The common base class for all built-in smart albums which can neither
 * be created to deleted, but always exists.
 * Photos cannot explicitly be added or removed from these albums, but
 * photos belong to these albums due to certain properties like being
 * starred, being recently added, etc.
 *
 * @property string $id
 */
abstract class BaseSmartAlbum implements BaseAlbum
{
	use HasAttributes;
	use HasSimpleRelationships;
	use UTCBasedTimes {
		UTCBasedTimes::serializeDate insteadof HasAttributes;
		UTCBasedTimes::fromDateTime insteadof HasAttributes;
		UTCBasedTimes::asDateTime insteadof HasAttributes;
	}

	protected $casts = [
		'public' => MustNotSetCast::class,
		'downloadable' => MustNotSetCast::class,
		'share_button_visible' => MustNotSetCast::class,
	];

	protected $appends = [
		'public',
		'downloadable',
		'share_button_visible',
		'thumb',
	];

	protected function __construct(string $id, string $title, bool $public)
	{
		$this->attributes['id'] = $id;
		$this->attributes['title'] = $title;
		$this->attributes['public'] = $public;
		$this->attributes['downloadable'] = Configs::get_value('downloadable', '0') === '1';
		$this->attributes['share_button_visible'] = Configs::get_value('share_button_visible', '0') === '1';
	}

	/**
	 * Serializes this object into an array.
	 *
	 * @return array The serialized properties of this object
	 */
	public function toArray(): array
	{
		return array_merge($this->attributesToArray(), $this->relationsToArray());
	}

	/**
	 * Serializes this object into an array.
	 *
	 * @return array The serialized properties of this object
	 *
	 * @see BaseSmartAlbum::toArray()
	 */
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}

	protected function getThumbAttribute(): ?Thumb
	{
		// Note, `photos()` already applies a "security filter" and
		// only returns photos which are accessible by the current
		// user
		return Thumb::createFromPhotoRelation(
			$this->photos(),
			Configs::get_value('sorting_Photos_col'),
			Configs::get_value('sorting_Photos_order')
		);
	}

	/**
	 * "Deletes" a built-in smart album.
	 *
	 * Typically, a built-in smart album cannot be deleted, hence this
	 * default implementation always returns false.
	 *
	 * @return bool true on success
	 */
	public function delete(): bool
	{
		return false;
	}
}
