<?php

namespace App\Contracts\Http\Requests;

interface HasCompactBoolean
{
	public function is_compact(): bool;
}
