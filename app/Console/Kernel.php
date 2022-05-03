<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

class Kernel extends ConsoleKernel
{
	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param Schedule $schedule
	 *
	 * @return void
	 *
	 * @throws BindingResolutionException
	 */
	protected function schedule(Schedule $schedule)
	{
		$schedule->command('lychee:photos_added_notification')->weekly();
	}

	/**
	 * Register the commands for the application.
	 *
	 * @return void
	 *
	 * @throws \ReflectionException
	 * @throws \RuntimeException
	 * @throws DirectoryNotFoundException
	 */
	protected function commands()
	{
		$this->load(__DIR__ . '/Commands');
	}
}
