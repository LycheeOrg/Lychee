<?php

namespace App\SmartAlbums;

use App\Configs;

class StarredAlbum extends SmartAlbum
{
	public function __construct()
	{
	}

	public function is_full_photo_visible()
	{
		return false;
	}

	public function is_downloadable()
	{
		return Configs::get_value('downloadable', '0') == '1';
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

	public function get_license()
	{
		return 'none';
	}
}
