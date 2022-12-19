<?php

namespace App\Contracts\Http\Requests;

interface HasPhotoIDs
{
	/**
	 * @return string[]
	 */
	public function photoIDs(): array;
}
