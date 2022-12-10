<?php

namespace App\Http\Requests\Contracts;

use Illuminate\Support\Carbon;

interface HasDate
{
	/**
	 * @return Carbon|null
	 */
	public function requestDate(): ?Carbon;
}
