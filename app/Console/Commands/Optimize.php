<?php

namespace App\Console\Commands;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Console\OptimizeCommand;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Exception\ExceptionInterface as ConsoleException;

class Optimize extends OptimizeCommand
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'optimize
		{--clever : Only (re-)creates cache if cache has already been created before and if not in production mode}
		{--force : Don\'t ask for confirmation, if cache shall be created in debug mode (same behaviour as original Laravel command)}';

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
		$shallBeClever = $this->hasOption('clever') && $this->option('no-clever') === true;
		$shallEnforce = $this->hasOption('force') && $this->option('force') === true;
		$hasPreviousCache = file_exists($this->laravel->getCachedConfigPath()) || file_exists($this->laravel->getCachedRoutesPath());

		$this->call('optimize:clear');

		if ($shallBeClever && !$hasPreviousCache) {
			return;
		}

		if ($this->isNonProductive() && !$shallEnforce) {
			$this->alert('Application In Production!');

			$hasConfirmed = $this->confirm('Do you really wish to run this command?');

			if (!$hasConfirmed) {
				$this->comment('Command Canceled!');

				return;
			}
		}

		parent::handle();
	}

	/**
	 * Checks whether Lychee is running in a non-prduction environment.
	 *
	 * Note, this method deliberately tends to `true` in case of doubt.
	 * This means if anything indicates that the setup might be used for
	 * developing or testing purposes, the result is `true`.
	 * Such indicators are the environment setting, enabled debug mode or
	 * debug bar, installed PhpUnit or PhpStan.
	 * If we are not in production mode, this command ask for confirmation,
	 * and we rather ask one time too often than not.
	 *
	 * @return bool `true`, if the
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
