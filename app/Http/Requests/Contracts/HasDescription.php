<?php

namespace App\Http\Requests\Contracts;

interface HasDescription
{
	public const DESCRIPTION_ATTRIBUTE = 'description';

	/**
	 * @return string|null
	 */
	public function description(): ?string;
}