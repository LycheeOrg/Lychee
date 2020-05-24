<?php

namespace App\SmartAlbums;

use App\Configs;
use App\ModelFunctions\SessionFunctions;
use App\Photo;

class StarredAlbum extends SmartAlbum
{
	/**
	 * @var SessionFunctions
	 */
	private $sessionFunctions;

	public function __construct(SessionFunctions $sessionFunctions)
	{
		$this->sessionFunctions = $sessionFunctions;
	}

	public function get_photos()
	{
		return Photo::select_stars(Photo::OwnedBy($this->sessionFunctions->id()));
	}

	public function is_share_button_visible()
	{
		return Configs::get_value('share_button_visible', '0') == '1';
	}

	// Parse date
	public function str_min_takestamp()
	{
		return '';
	}

	public function str_max_takestamp()
	{
		return '';
	}
}
