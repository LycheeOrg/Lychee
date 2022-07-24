<?php

namespace App\Actions\Album;

use App\Exceptions\ModelDBException;
use App\Models\TagAlbum;
use Illuminate\Support\Facades\Auth;

class CreateTagAlbum extends Action
{
	/**
	 * Create a new smart album based on tags.
	 *
	 * @param string   $title
	 * @param string[] $show_tags
	 *
	 * @return TagAlbum
	 *
	 * @throws ModelDBException
	 */
	public function create(string $title, array $show_tags): TagAlbum
	{
		$album = new TagAlbum();
		$album->title = $title;
		$album->show_tags = $show_tags;
		$album->owner_id = Auth::authenticate()->id;
		$album->save();

		return $album;
	}
}
