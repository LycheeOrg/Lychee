<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

class Kernel extends ConsoleKernel
{
	/**
	 * Define the application's command schedule.
	 *
	 * @param Schedule $schedule
	 *
	 * @return void
	 *
	 * @throws BindingResolutionException
	 */
	protected function schedule(Schedule $schedule): void
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
	protected function commands(): void
	{
		$this->load(__DIR__ . '/Commands');
	}
}
