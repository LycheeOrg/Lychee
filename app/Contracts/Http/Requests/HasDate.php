<?php

namespace App\Contracts\Http\Requests;

use Illuminate\Support\Carbon;

interface HasDate
{
	/**
	 * @return Carbon|null
	 */
	public function requestDate(): ?Carbon;
}
