<?php

namespace App\Contracts\Http\Requests;

interface HasNote
{
	/**
	 * @return string|null
	 */
	public function note(): ?string;
}