<?php

namespace App\Actions\InstallUpdate;

use App\Actions\InstallUpdate\Pipes\AllowMigrationCheck;
use App\Actions\InstallUpdate\Pipes\BranchCheck;
use App\Actions\InstallUpdate\Pipes\ComposerCall;
use App\Actions\InstallUpdate\Pipes\GitPull;
use App\Actions\InstallUpdate\Pipes\Migrate;
use Illuminate\Pipeline\Pipeline;
use Safe\Exceptions\PcreException;
use function Safe\preg_replace;

class Apply
{
	/**
	 * @var array<int,string> application of the updates
	 */
	private array $pipes = [
		BranchCheck::class,
		AllowMigrationCheck::class,
		GitPull::class,
		Migrate::class,
		ComposerCall::class,
	];

	/**
	 * Applies the migration:
	 * 1. git pull
	 * 2. artisan migrate.
	 *
	 * @return array<int,string> the per-line console output
	 *
	 * @throws PcreException
	 */
	public function run(): array
	{
		$output = [];

		$output = app(Pipeline::class)
			->send($output)
			->through($this->pipes)
			->thenReturn();

		return preg_replace('/\033[[][0-9]*;*[0-9]*;*[0-9]*m/', '', $output);
	}
}
