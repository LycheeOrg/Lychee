<?php

namespace App\Actions\Photo;

use App\Models\Photo;

class Prepare extends SymLinker
{
	/**
	 * @param Photo $photo
	 *
	 * @return array
	 */
	public function do(Photo $photo): array
	{
		$return = $photo->toReturnArray();

		$this->symLinkFunctions->getUrl($photo, $return);

		return $return;
	}
}
