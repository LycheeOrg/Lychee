<?php

namespace App\SmartAlbums;

use App\Models\Configs;
use Illuminate\Support\Collection as BaseCollection;

class SmartAlbum extends BareSmartAlbum
{
	/**
	 * @var string
	 */
	public $description = '';

	/**
	 * fake cover.
	 */
	public $cover = null;

	/**
	 * fake password string.
	 *
	 * @var string
	 */
	public $password = '';

	/**
	 * fake password string.
	 *
	 * @var string
	 */
	public $license = '';

	/*------------------------- BOOLEANS --------------------------------- */
	public function is_full_photo_visible(): bool
	{
		return false;
	}

	public function isLeaf(): bool
	{
		return true;
	}

	public function is_downloadable(): bool
	{
		return Configs::get_value('downloadable', '0') == '1';
	}

	public function is_share_button_visible(): bool
	{
		return Configs::get_value('share_button_visible', '0');
	}

	/*------------------------- STRINGS --------------------------------- */

	/*------------------------- GETTERS --------------------------------- */
	public function children()
	{
		return null;
	}

	public function get_children()
	{
		return new BaseCollection();
	}
}
