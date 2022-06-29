<?php

namespace App\Actions\Diagnostics\Checks;

use App\Contracts\DiagnosticCheckInterface;
use App\Facades\Helpers;
use Illuminate\Support\Facades\Storage;

class BasicPermissionCheck implements DiagnosticCheckInterface
{
	public function check(array &$errors): void
	{
		$this->folders($errors);
		$this->userCSS($errors);
	}

	public function folders(array &$errors): void
	{
		$paths = ['big', 'medium', 'small', 'thumb', 'import', ''];

		foreach ($paths as $path) {
			$p = Storage::path($path);
			if (!Helpers::hasPermissions($p)) {
				$errors[] = "Error: '" . $p . "' is missing or has insufficient read/write privileges";
			}
		}
	}

	public function userCSS(array &$errors): void
	{
		$p = Storage::disk('dist')->path('user.css');
		if (!Helpers::hasPermissions($p)) {
			$errors[] = "Warning: '" . $p . "' does not exist or has insufficient read/write privileges.";
			$p = Storage::disk('dist')->path('');
			if (!Helpers::hasPermissions($p)) {
				$errors[] = "Warning: '" . $p . "' has insufficient read/write privileges.";
			}
		}
	}
}
