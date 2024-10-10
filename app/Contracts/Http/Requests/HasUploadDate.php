<?php

namespace App\Contracts\Http\Requests;

use Illuminate\Support\Carbon;

interface HasUploadDate
{
	/**
	 * @return Carbon|null
	 */
	public function uploadDate(): ?Carbon;
}
