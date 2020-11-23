<?php

namespace App\SmartAlbums;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;

class PublicAlbum extends SmartAlbum
{
	public function get_title()
	{
		return 'public';
	}

	public function get_photos(): Builder
	{
		return Photo::public()->where(fn ($q) => $this->filter($q));
	}
}
