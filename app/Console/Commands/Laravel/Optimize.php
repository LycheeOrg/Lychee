<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Console\Commands\Laravel;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Console\OptimizeCommand;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Exception\ExceptionInterface as ConsoleException;
use Symfony\Component\Console\Exception\InvalidOptionException;

/**
 * Improves the original "optimize" command provided by the framework.
 *
 * There are three improvements:
 *
 *  1) This command explicitly clears the old cache before building a new one.
 *     Note that rebuilding a new cache is not sufficient, because this
 *     does *not* overwrite the entire cache but some leftovers may remain.
 *     This actually looks like an oversight and bug in the original command.
 *  2) This command adds a "clever" mode which only rebuilds a new cache if
 *     a previous cache has existed.
 *     We use this in our install/update scripts in order to rebuild the cache
 *     after installation/update without enforcing to use a cache for everyone.
 *  3) This command adds a confirmation, if the user requests to build a cache
 *     for non-productive environments as this is most likely an error and
 *     undesired.
 *     The confirmation can be skipped by pre-selecting the answer via a
 *     command line option.
 */
class Optimize extends OptimizeCommand
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'optimize
		{--clever : Only (re-)creates cache if cache has already been created before and if not in production mode}
		{--dont-confirm= : [assume-yes|assume-no] Don\'t ask for confirmation, but silently assume yes or no; "assume-yes" yields same behaviour as the original Laravel command}';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 *
	 * @throws ConsoleException
	 * @throws BindingResolutionException
	 * @throws NotFoundExceptionInterface
	 * @throws ContainerExceptionInterface
	 */
	public function handle(): void
	{
		$shallBeClever = $this->hasOption('clever') && $this->option('clever') === true;
		$confirmationDefault = match ($this->option('dont-confirm')) {
			'assume-yes' => true,
			'assume-no' => false,
			null => null,
			default => throw new InvalidOptionException(sprintf('Unexpected option value %s for --dont-confirm', strval($this->option('dont-confirm')))),
		};
		$hasPreviousCache = file_exists($this->laravel->getCachedConfigPath()) || file_exists($this->laravel->getCachedRoutesPath());

		$this->call('optimize:clear');

		if ($shallBeClever && !$hasPreviousCache) {
			return;
		}

		if ($this->isNonProductive()) {
			$this->alert('Application not in Production!');

			$hasConfirmed = $confirmationDefault ?? $this->confirm('Do you really wish to run this command?');

			if (!$hasConfirmed) {
				$this->comment('Command Canceled!');

				return;
			}
		}

		parent::handle();
	}

	/**
	 * Checks whether Lychee is running in a non-production environment.
	 *
	 * Note, this method deliberately tends to `true` in case of doubt.
	 * This means if anything indicates that the setup might be used for
	 * developing or testing purposes, the result is `true`.
	 * Such indicators are the environment setting, enabled debug mode or
	 * debug bar, installed PhpUnit or PhpStan.
	 * If we are not in production mode, this command asks for confirmation,
	 * and we rather ask one time too often than not.
	 *
	 * @return bool `true`, if Lychee is found to run in non-production mode
	 *
	 * @throws BindingResolutionException
	 * @throws NotFoundExceptionInterface
	 * @throws ContainerExceptionInterface
	 */
	protected function isNonProductive(): bool
	{
		return
			'production' !== $this->getLaravel()->environment() ||
			true === config('app.debug', false) ||
			true === config('debugbar.enabled', false) ||
			file_exists(base_path('vendor/bin/phpunit')) ||
			file_exists(base_path('vendor/bin/phpstan')) ||
			file_exists(base_path('vendor/bin/phpstan.phar'));
	}
}
