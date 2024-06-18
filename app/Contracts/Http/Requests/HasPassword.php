<?php

declare(strict_types=1);

namespace App\Contracts\Http\Requests;

interface HasPassword
{
	/**
	 * @return string|null
	 */
	public function password(): ?string;
}
