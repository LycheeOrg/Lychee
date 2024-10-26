<?php

namespace App\Contracts\Http\Requests;

interface HasPhotoIds
{
	/**
	 * @return string[]
	 */
	public function photoIds(): array;
}
