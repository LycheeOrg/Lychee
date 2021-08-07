<?php

namespace App\Actions\Album;

use App\Models\Logs;

class SetNSFW extends Setter
{
	public function __construct()
	{
		parent::__construct();
		$this->property = 'nsfw';
	}

	public function do(string $albumID, ?string $_): bool
	{
		if ($this->albumFactory->isBuiltInSmartAlbum($albumID)) {
			Logs::warning(__FUNCTION__, __LINE__, 'NSFW tag is not possible on smart albums.');

			return false;
		}
		$album = $this->albumFactory->findOrFail($albumID);

		return $this->execute($album, ($album->nsfw != 1) ? 1 : 0);
	}
}
