<?php

declare(strict_types=1);

namespace App\Contracts\Http\Requests;

interface HasPhotoIDs
{
	/**
	 * @return string[]
	 */
	public function photoIDs(): array;
}
