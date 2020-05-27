<?php

namespace App\SmartAlbums;

use App\Configs;
use App\Photo;
use Illuminate\Database\Eloquent\Builder;

class PublicAlbum extends SmartAlbum
{
	public function get_title()
	{
		return 'public';
	}

	public function get_photos(): Builder
	{
		return Photo::select_public(Photo::OwnedBy($this->sessionFunctions->id()));
	}

	public function is_public()
	{
		return Configs::get_value('public_recent', '0') === '1';
	}
}
