<?php

namespace App\ModelFunctions;

use AccessControl;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\SymLink;
use Illuminate\Support\Facades\Storage;

class SymLinkFunctions
{
	/**
	 * @param Photo $photo
	 *
	 * @return SymLink|null
	 */
	public function find(Photo $photo): ?SymLink
	{
		if (Storage::getDefaultDriver() == 's3') {
			// @codeCoverageIgnoreStart
			return null;
			// @codeCoverageIgnoreEnd
		}
		if (Configs::get_value('SL_enable', '0') === '0') {
			return null;
		}

		if (AccessControl::is_admin() && Configs::get_value('SL_for_admin', '0') === '0') {
			// @codeCoverageIgnoreStart
			return null;
			// @codeCoverageIgnoreEnd
		}

		$sym = null;

		$sym = SymLink::where('photo_id', $photo->id)
			->orderBy('created_at', 'DESC')
			->first();
		if ($sym == null) {
			$sym = new SymLink();
			$sym->set($photo);
			$sym->save();
		}

		return $sym;
	}

	/**
	 * Get URLS of pictures.
	 *
	 * This method modifies the serialization of a photo such that the original URLs are replaced by symlinks.
	 * *Attention:* The passed $photo and the passed array $return which represents the serialization of the photo must
	 * match.
	 * It is the caller's responsibility to ensure that $return equals $photo->toReturnArray().
	 *
	 * @param Photo $photo  The photo that is going to be serialized
	 * @param array $return The serialization of the passed photo as returned by Photo#toReturnArray()
	 */
	public function getUrl(
		Photo $photo,
		array &$return
	) {
		$sym = $this->find($photo);
		if ($sym != null) {
			$sym->override($return);
		}
	}

	/**
	 * Clear the table of existing SymLinks.
	 *
	 * @return string
	 *
	 * @throws \Exception
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

	/**
	 * Remove outdated SymLinks.
	 *
	 * @return bool
	 */
	public function remove_outdated()
	{
		$symlinks = SymLink::where('created_at', '<', now()->subDays(intval(Configs::get_value('SL_life_time_days', '3')))->toDateTimeString())->get();
		$success = true;
		foreach ($symlinks as $symlink) {
			// it may be faster to just do the unlink and then one query for all the delete.
			$success &= $symlink->delete();
		}

		return $success;
	}
}
