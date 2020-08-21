<?php

namespace App;

use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\SessionFunctions;
use App\SmartAlbums\SmartAlbum;

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
	public function __construct(AlbumFunctions $albumFunctions, SessionFunctions $sessionFunctions, $title)
	{
		parent::__construct($albumFunctions, $sessionFunctions);
		$this->title = 'tag-' . $title;
	}

	public function get_title()
	{
		return $this->title;
	}

	public function get_photos()
	{
		return Photo::whereTags($this->tags)->where(function ($q) {
			return $this->filter($q);
		});
	}

	public function is_public()
	{
		return true;
	}
}