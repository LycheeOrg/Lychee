<?php

namespace App\SmartAlbums;

use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\SessionFunctions;
use App\Photo;
use Illuminate\Database\Eloquent\Builder;

class TagAlbum extends SmartAlbum
{
	public $tags = [];

	/**
	 * TagAlbum constructor.
	 *
	 * @param AlbumFunctions   $albumFunctions
	 * @param SessionFunctions $sessionFunctions
	 * @param $title
	 */
	public function __construct(AlbumFunctions $albumFunctions, SessionFunctions $sessionFunctions)
	{
		parent::__construct($albumFunctions, $sessionFunctions);
	}

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

		return $sql->where(function ($q) {
			return $this->filter($q);
		});
	}

	public function is_public()
	{
		return $this->public;
	}
}