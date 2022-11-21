<?php

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\Models\User;
use Closure;

class AdminUserExistsCheck implements DiagnosticPipe
{
	public function handle(array &$data, Closure $next): array
	{
		$admin = User::query()->find(0);
		if ($admin === null) {
			$data[] = 'Error: User Admin not found in database. Please run: "php artisan lychee:reset_admin"';
		}

		return $next($data);
	}
}
