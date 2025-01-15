<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Console\Commands\Laravel;

use App\Actions\Diagnostics\Pipes\Checks\PHPVersionCheck;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Exception\ExceptionInterface as ConsoleException;
use Symfony\Component\Console\Exception\RuntimeException;

/**
 * Improves the original "migrate" command provided by the framework.
 *
 * We check that the version of php is up to date before applying any changes to databases.
 */
class Migrate extends MigrateCommand
{
	/**
	 * Create a new migration command instance.
	 * See: https://stackoverflow.com/questions/45938615/custom-laravel-migration-command-illuminate-database-migrations-migrationrepos.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct(app('migrator'), resolve(Dispatcher::class));
	}

	/**
	 * Execute the console command.
	 *
	 * @return int
	 *
	 * @throws ConsoleException
	 * @throws BindingResolutionException
	 * @throws NotFoundExceptionInterface
	 * @throws ContainerExceptionInterface
	 */
	public function handle(): int
	{
		$current_php_version = floatval(phpversion());
		if ($current_php_version <= PHPVersionCheck::PHP_ERROR) {
			throw new RuntimeException(sprintf('PHP %01.1f is out of date, please update first to at least %01.1f.', $current_php_version, PHPVersionCheck::PHP_WARNING));
		}

		return parent::handle();
	}
}