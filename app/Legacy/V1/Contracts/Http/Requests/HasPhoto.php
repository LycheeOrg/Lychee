<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

use App\Models\Photo;

interface HasPhoto
{
	/**
	 * @return Photo|null
	 */
	public function photo(): ?Photo;
}
