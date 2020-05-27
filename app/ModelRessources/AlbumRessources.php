<?php

namespace App\ModelRessources;

use App\Album;
use Illuminate\Support\Facades\Hash;

class AlbumRessources
{
	/**
	 * Given a password, check if it matches albums password.
	 *
	 * @param string $password
	 *
	 * @return bool returns when album is public
	 */
	public function checkPassword(Album $album, string $password)
	{
		// album password is empty or input is correct.
		return $album->password == '' || Hash::check($password, $album->password);
	}
}
