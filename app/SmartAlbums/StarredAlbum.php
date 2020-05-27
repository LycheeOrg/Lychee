<?php

namespace App\SmartAlbums;

use App\Configs;
use App\Photo;
use Illuminate\Database\Eloquent\Builder;

class StarredAlbum extends SmartAlbum
{
	public function get_title()
	{
		return 'starred';
	}

	public function get_photos(): Builder
	{
		$sql = Photo::stars()->where(function ($query) {
			if (!$this->sessionFunctions->is_admin()) {
				$query = $query->whereIn('album_id', $this->albumIds);
			}
			if ($this->sessionFunctions->is_logged_in() && $this->sessionFunctions->id() > 0) {
				$query = $query->orWhere('owner_id', '=', $this->sessionFunctions->id());
			}
		});

		return Photo::set_order($sql);
	}

	public function is_public()
	{
		return Configs::get_value('public_starred', '0') === '1';
	}
}
