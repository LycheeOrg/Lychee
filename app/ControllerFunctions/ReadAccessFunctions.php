<?php


namespace App\ControllerFunctions;


use App\Album;
use App\ModelFunctions\SessionFunctions;
use App\Photo;

class ReadAccessFunctions
{

	/**
	 * @var SessionFunctions
	 */
	private $sessionFunctions;



	/**
	 * @param SessionFunctions $sessionFunctions
	 */
	public function __construct(SessionFunctions $sessionFunctions)
	{
		$this->sessionFunctions = $sessionFunctions;
	}



	/**
	 * Check if a (public) user has access to an album
	 * if 0 : album does not exists
	 * if 1 : access is granted
	 * if 2 : album is private
	 * if 3 : album is password protected and require user input
	 *
	 * @param $albumID
	 * @return int
	 */
	public function albums($albumID)
	{
		// if we are logged in then we have access
		// we do not use this function to check if there is proper acess in case of sharing... yet?
		if ($this->sessionFunctions->is_logged_in()) {
			return 1; // access granted
		}


		$album = Album::find($albumID);
		if ($album == null) {
			return 0;  // Does not exist
		}
		if ($album->public != 1) {
			return 2;  // Warning: Album private!
		}
		if ($album->password == '') {
			return 1;  // access granted
		}

		if ($this->sessionFunctions->has_visible_album($albumID)) {
			return 1;  // access granted
		}

		return 3;      // Please enter password first. // Warning: Wrong password!

	}



	/**
	 * Check if a (public) user has access to a picture.
	 *
	 * @param Photo $photo
	 * @return bool
	 */
	public function photo(Photo $photo)
	{
		if ($this->sessionFunctions->is_logged_in())
			return true;
		if ($photo->get_public() == '1')
			return true;
		if ($this->albums($photo->album_id) === 1)
			return true;
	}
}