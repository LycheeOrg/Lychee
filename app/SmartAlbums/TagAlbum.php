<?php

namespace App\SmartAlbums;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;

class TagAlbum extends SmartAlbum
{
	public function get_title()
	{
		return $this->title;
	}

	public function get_photos(): Builder
	{
		$sql = Photo::query();

		$tags = explode(',', $this->showtags);
		foreach ($tags as $tag) {
			$sql = $sql->where('tags', 'like', '%' . trim($tag) . '%');
		}

		return $sql->where(fn ($q) => $this->filter($q));
	}

	public function is_public()
	{
		return $this->public;
	}
}
