<?php

namespace App\Actions\Diagnostics\Checks;

use App\Contracts\DiagnosticCheckInterface;
use App\Models\User;

class AdminUserExistsCheck implements DiagnosticCheckInterface
{
	public function check(array &$errors): void
	{
		$admin = User::query()->find(0);
		if ($admin == null) {
			$errors[] = 'Error: User Admin not found in database. Please run: "php artisan lychee:reset_admin"';
		}
	}
}
