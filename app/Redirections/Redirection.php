<?php

namespace App\Redirections;

use Illuminate\Http\RedirectResponse;

interface Redirection
{
	public static function go(): RedirectResponse;
}
