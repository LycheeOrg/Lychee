<?php

namespace App\Http\Requests\Contracts;

interface HasPhotoIDs
{
	/**
	 * @return string[]
	 */
	public function photoIDs(): array;
}
