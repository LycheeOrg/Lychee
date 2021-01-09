<?php

namespace App\Models\Extensions;

use App\Actions\Photo\Extensions\Constants;

trait PhotoBooleans
{
	use Constants;

	/**
	 * Check if a photo already exists in the database via its checksum.
	 *
	 * ! Does not require the Photo Object. Should be moved.
	 *
	 * @param string $checksum
	 * @param $photoID
	 *
	 * @return Photo|bool|Builder|Model|object
	 */
	public function isDuplicate(string $checksum, $photoID = null)
	{
		$sql = $this->where(function ($q) use ($checksum) {
			$q->where('checksum', '=', $checksum)
				->orWhere('livePhotoChecksum', '=', $checksum);
		});
		if (isset($photoID)) {
			$sql = $sql->where('id', '<>', $photoID);
		}

		return $sql->first() ?? false;
	}

	/**
	 * We are checking if the beginning of the type string is
	 * video.
	 *
	 * type contains the mime informations
	 */
	public function isVideo(): bool
	{
		return $this->isValidVideoType($this->type);
	}
}
