<?php

namespace App\SmartAlbums;

use App\Photo;
use Illuminate\Database\Eloquent\Builder;

class UnsortedAlbum extends SmartAlbum
{
	public function get_title()
	{
		return 'unsorted';
	}

	public function get_photos(): Builder
	{
		return Photo::select_unsorted(Photo::OwnedBy($this->sessionFunctions->id()));
	}

	public function is_public()
	{
		return false;
	}
}
