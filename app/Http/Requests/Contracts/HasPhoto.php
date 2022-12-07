<?php

namespace App\Http\Requests\Contracts;

use App\Models\Photo;

interface HasPhoto
{
	/**
	 * @return Photo|null
	 */
	public function photo(): ?Photo;
}
