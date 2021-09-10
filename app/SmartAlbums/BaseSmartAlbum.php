<?php

namespace App\SmartAlbums;

use App\Casts\MustNotSetCast;
use App\Contracts\AbstractAlbum;
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
