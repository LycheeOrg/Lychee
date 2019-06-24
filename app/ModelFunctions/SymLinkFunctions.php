<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\ModelFunctions;

use App;
use App\Photo;
use App\Configs;
use App\SymLink;
use Storage;

class SymLinkFunctions
{
	/**
	 * @param Photo $photo
	 *
	 * @return SymLink|null
	 */
	public function find(Photo $photo)
	{
		$sym = null;

		if (Storage::getDefaultDriver() == 's3' || Configs::get_value('enable_picture_symlink', '0') === '0') {
			return $sym;
		}

		$sym = SymLink::where('photo_id', $photo->id)->orderBy('created_at', 'DESC')->first();
		if ($sym == null) {
			$sym = new SymLink();
			$sym->set($photo);
			$sym->save();
		}

		return $sym;
	}

	/**
	 * get URLS of pictures.
	 *
	 * @param Photo $photo
	 * @param $return
	 */
	public function getUrl(Photo $photo, &$return)
	{
		$sym = $this->find($photo);
		if ($sym != null) {
			$sym->override($return);
		}
	}

	/**
	 * Clear the table of existing SymLinks.
	 *
	 * @return string
	 */
	public function clearSymLink()
	{
		$symlinks = SymLink::all();
		$no_error = true;
		foreach ($symlinks as $symlink) {
			$no_error &= $symlink->delete();
		}

		return $no_error ? 'true' : 'false';
	}
}
