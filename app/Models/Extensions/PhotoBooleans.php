<?php

namespace App\Models\Extensions;

use App\Actions\Photo\Extensions\Constants;

trait PhotoBooleans
{
	use Constants;

	/**
	 * We are checking if the beginning of the type string is
	 * video.
	 *
	 * type contains the mime information
	 */
	public function isVideo(): bool
	{
		if (empty($this->type)) {
			throw new \BadFunctionCallException('Photo::isVideo() must not be called before Photo::$type has been set');
		}

		return $this->isValidVideoType($this->type);
	}

	public function isRaw(): bool
	{
		if (empty($this->type)) {
			throw new \BadFunctionCallException('Photo::isRaw() must not be called before Photo::$type has been set');
		}

		return $this->type == 'raw';
	}
}
