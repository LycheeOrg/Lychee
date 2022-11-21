<?php

namespace App\Actions\Diagnostics;

use App\Actions\Diagnostics\Pipes\Checks\AdminUserExistsCheck;
use App\Actions\Diagnostics\Pipes\Checks\BasicPermissionCheck;
use App\Actions\Diagnostics\Pipes\Checks\ConfigSanityCheck;
use App\Actions\Diagnostics\Pipes\Checks\DBSupportCheck;
use App\Actions\Diagnostics\Pipes\Checks\GDSupportCheck;
use App\Actions\Diagnostics\Pipes\Checks\ImageOptCheck;
use App\Actions\Diagnostics\Pipes\Checks\IniSettingsCheck;
use App\Actions\Diagnostics\Pipes\Checks\LycheeDBVersionCheck;
use App\Actions\Diagnostics\Pipes\Checks\PHPVersionCheck;
use App\Actions\Diagnostics\Pipes\Checks\TimezoneCheck;
use Illuminate\Pipeline\Pipeline;

class Errors extends Diagnostics
{
	/**
	 * The array of class pipes.
	 *
	 * @var array<int,class-string>
	 */
	public $pipes = [
		AdminUserExistsCheck::class,
		BasicPermissionCheck::class,
		ConfigSanityCheck::class,
		DBSupportCheck::class,
		GDSupportCheck::class,
		ImageOptCheck::class,
		IniSettingsCheck::class,
		LycheeDBVersionCheck::class,
		PHPVersionCheck::class,
		TimezoneCheck::class,
	];

	/**
	 * Return the list of error which are currently breaking Lychee.
	 *
	 * @param string[] $skip class names of checks that will be skipped
	 *
	 * @return string[] array of messages
	 */
	public function get(array $skip = []): array
	{
		$filteredPipes = collect($this->pipes);
		$this->pipes = $filteredPipes->reject(fn ($p) => in_array((new \ReflectionClass($p))->getShortName(), $skip, true))->all();

		/** @var string[] $errors */
		$errors = [];

		return app(Pipeline::class)
			->send($errors)
			->through($this->pipes)
			->thenReturn();
	}
}
