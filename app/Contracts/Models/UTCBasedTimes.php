<?php

declare(strict_types=1);

namespace App\Contracts\Models;

use Illuminate\Support\Carbon;

interface UTCBasedTimes
{
	public function asDateTime($value): Carbon;
}