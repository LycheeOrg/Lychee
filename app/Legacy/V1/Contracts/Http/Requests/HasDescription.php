<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

interface HasDescription
{
	/**
	 * @return string|null
	 */
	public function description(): ?string;
}