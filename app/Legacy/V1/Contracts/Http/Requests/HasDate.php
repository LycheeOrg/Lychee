<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

use Illuminate\Support\Carbon;

interface HasDate
{
	/**
	 * @return Carbon|null
	 */
	public function requestDate(): ?Carbon;
}
