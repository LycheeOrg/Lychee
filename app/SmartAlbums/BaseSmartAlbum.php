<?php

namespace App\SmartAlbums;

use App\Contracts\AbstractAlbum;
use App\Exceptions\InvalidPropertyException;
use App\Models\Configs;
use App\Models\Extensions\Thumb;

/**
 * Class BaseSmartAlbum.
 *
 * The common base class for all built-in smart albums which can neither
 * be created nor deleted, but always exists.
 * Photos cannot explicitly be added or removed from these albums.
 * Photos belong to these albums due to certain properties like being
 * starred, being recently added, etc.
 *
 * @property string $id
 */
abstract class BaseSmartAlbum extends FakeModel implements AbstractAlbum
{
	/**
	 * Note, due to Laravel's stupidity and PHP type mangling, boolean values
	 * always need an explicit cast, even if they are already stored as proper
	 * booleans in `$this->attributes`. :-(
	 * Otherwise, a `false` value will be reported as `null`. Yikes!
	 *
	 * @var string[] the list of attributes which needs casting to the correct
	 *               type when their getter is invoked
	 */
	protected $casts = [
		'is_public' => 'boolean',
		'is_downloadable' => 'boolean',
		'is_share_button_visible' => 'boolean',
	];

	protected $appends = [
		'thumb',
	];

	protected function __construct(string $id, string $title, bool $isPublic)
	{
		$this->attributes['id'] = $id;
		$this->attributes['title'] = $title;
		$this->attributes['is_public'] = $isPublic;
		$this->attributes['is_downloadable'] = Configs::get_value('downloadable', '0') === '1';
		$this->attributes['is_share_button_visible'] = Configs::get_value('share_button_visible', '0') === '1';
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
			$this->photos(),
			Configs::get_value('sorting_Photos_col'),
			Configs::get_value('sorting_Photos_order')
		);
	}
}
