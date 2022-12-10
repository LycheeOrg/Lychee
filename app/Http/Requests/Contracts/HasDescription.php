<?php

namespace App\Http\Requests\Contracts;

interface HasDescription
{
	/**
	 * @return string|null
	 */
	public function description(): ?string;
}