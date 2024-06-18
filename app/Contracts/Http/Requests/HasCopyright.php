<?php

declare(strict_types=1);

namespace App\Contracts\Http\Requests;

interface HasCopyright
{
	/**
	 * @return string|null
	 */
	public function copyright(): ?string;
}
