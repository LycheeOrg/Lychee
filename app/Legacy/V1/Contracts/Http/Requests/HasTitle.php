<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

interface HasTitle
{
	/**
	 * @return string|null
	 */
	public function title(): ?string;
}
