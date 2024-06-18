<?php

declare(strict_types=1);

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class AdminUserExistsCheck implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if (!Schema::hasTable('users')) {
			// @codeCoverageIgnoreStart
			return $next($data);
			// @codeCoverageIgnoreEnd
		}

		$numberOfAdmin = User::query()->where('may_administrate', '=', true)->count();
		if ($numberOfAdmin === 0) {
			// @codeCoverageIgnoreStart
			$data[] = 'Error: User Admin not found in database. Please run: "php lychee:create_user {username} {password}"';
			// @codeCoverageIgnoreEnd
		}

		return $next($data);
	}
}
