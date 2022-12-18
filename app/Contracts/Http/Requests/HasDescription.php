<?php

namespace App\Contracts\Http\Requests;

interface HasDescription
{
	/**
	 * @return string|null
	 */
	public function description(): ?string;
}