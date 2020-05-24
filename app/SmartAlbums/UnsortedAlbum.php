<?php

namespace App\SmartAlbums;

use App\ModelFunctions\SessionFunctions;
use App\Photo;

class UnsortedAlbum extends SmartAlbum
{
	/**
	 * @var SessionFunctions
	 */
	public $sessionFunctions;

	public function __construct(SessionFunctions $sessionFunctions)
	{
		$this->sessionFunctions = $sessionFunctions;
	}

	public function get_photos()
	{
		return Photo::select_unsorted(Photo::OwnedBy($this->sessionFunctions->id()));
	}
}
