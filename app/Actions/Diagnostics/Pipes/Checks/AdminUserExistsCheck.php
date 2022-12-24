<?php

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\Models\User;

class AdminUserExistsCheck implements DiagnosticPipe
{
	public function handle(array &$data, \Closure $next): array
	{
		$numberOfAdmin = User::query()->where('may_administrate', '=', true)->count();
		if ($numberOfAdmin === 0) {
			$data[] = 'Error: User Admin not found in database. Please run: "php lychee:create_user {username} {password}"';
		}

		return $next($data);
	}
}