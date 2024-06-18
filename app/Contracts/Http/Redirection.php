<?php

declare(strict_types=1);

namespace App\Contracts\Http;

use Illuminate\Http\RedirectResponse;

interface Redirection
{
	public static function go(): RedirectResponse;
}
