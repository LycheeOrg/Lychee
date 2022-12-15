<?php

namespace App\Contracts\Http;

use Illuminate\Http\RedirectResponse;

interface Redirection
{
	public static function go(): RedirectResponse;
}
