<?php

namespace App\Http\Requests\Contracts;

use Illuminate\Support\Carbon;

interface HasDate
{
	public const DATE_ATTRIBUTE = 'date';

	/**
	 * @return Carbon|null
	 */
	public function requestDate(): ?Carbon;
}
