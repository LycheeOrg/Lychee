<?php

declare(strict_types=1);

namespace App\Contracts\Http\Requests;

use Illuminate\Support\Carbon;

interface HasDate
{
	/**
	 * @return Carbon|null
	 */
	public function requestDate(): ?Carbon;
}
