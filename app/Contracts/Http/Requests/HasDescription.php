<?php

declare(strict_types=1);

namespace App\Contracts\Http\Requests;

interface HasDescription
{
	/**
	 * @return string|null
	 */
	public function description(): ?string;
}