<?php

namespace App\Contracts\Http\Requests;

interface HasSeStatusBoolean
{
	public function is_se(): bool;
}
