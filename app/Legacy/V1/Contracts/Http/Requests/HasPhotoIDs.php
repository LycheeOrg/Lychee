<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

interface HasPhotoIDs
{
	/**
	 * @return string[]
	 */
	public function photoIDs(): array;
}
