<?php

namespace App\SmartAlbums;

use App\Photo;
use Illuminate\Database\Eloquent\Builder;

class RecentAlbum extends SmartAlbum
{
	public function get_title()
	{
		return 'recent';
	}

	public function get_photos(): Builder
	{
		return Photo::select_recent(Photo::OwnedBy($this->sessionFunctions->id()));
	}

	public function is_public()
	{
		return false;
	}
}
