<?php

namespace App\Http\Requests\Contracts;

interface HasTitle
{
	/**
	 * @return string|null
	 */
	public function title(): ?string;
}
