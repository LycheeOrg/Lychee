<?php

namespace App\SmartAlbums;

use App\ModelFunctions\SessionFunctions;
use App\Photo;

class RecentAlbum extends SmartAlbum
{
	/**
	 * @var SessionFunctions
	 */
	private $sessionFunctions;

	public function __construct(SessionFunctions $sessionFunctions)
	{
		$this->sessionFunctions = $sessionFunctions;
	}

	public function get_photos()
	{
		return Photo::select_recent(Photo::OwnedBy($this->sessionFunctions->id()));
	}
}
