<?php

namespace App\ModelFunctions;

use App\Album;
use Illuminate\Support\Facades\Session;

class AlbumFunctions
{
	/**
	 * @param  string $title
	 * @param  int $parent_id
	 * @return Album
	 */
	public function create(string $title, $parent_id): Album
	{
		$num = Album::where('id', '=', $parent_id)->count();
		// id cannot be 0, so by definition if $parent_id is 0 then...

		$album = new Album();
		$album->id = Helpers::generateID();
		$album->title = $title;
		$album->description = '';
		$album->owner_id = Session::get('UserID');
		$album->parent_id = $num == 0 ? null : $parent_id;
		$album->save();

		return $album;
	}
}
