<?php

namespace App\SmartAlbums;

use App\Configs;
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
		// php7.4: return Photo::recent()->where(fn ($q) => $this->filter($q));
		return Photo::recent()->where(function ($q) {
			return $this->filter($q);
		});
	}

	public function is_public()
	{
		return Configs::get_value('public_recent', '0') === '1';
	}
}
