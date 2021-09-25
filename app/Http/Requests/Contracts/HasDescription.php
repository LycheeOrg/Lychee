<?php

namespace App\Http\Requests\Contracts;

interface HasDescription
{
	const DESCRIPTION_ATTRIBUTE = 'description';

	/**
	 * @return string|null
	 */
	public function description(): ?string;
}