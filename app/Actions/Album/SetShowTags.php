<?php

namespace App\Actions\Album;

use App\Models\Logs;

class SetShowTags extends Setter
{
	public function __construct()
	{
		$this->property = 'showtags';
	}

	public function do(string $albumID, string $value): bool
	{
		$album = $this->albumFactory->make($albumID);

		if (!$album->is_tag_album()) {
			Logs::error(__METHOD__, __LINE__, 'Could not change show tags on non tag album');

			return false;
		}

		return $this->execute($album, $value);
	}
}
