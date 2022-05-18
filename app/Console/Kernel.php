<?php

namespace App\Console;

use App\Models\Configs;
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
		$ldap_update = $schedule->command('lychee:LDAP_update_all_users');
		$ldap_update_users = Configs::get_value('ldap_update_users');
		if ($ldap_update_users > 0) {
			if ($ldap_update_users < 60) {
				$m = sprintf('*/%s', $ldap_update_users);
				$h = '*';
			} elseif ($ldap_update_users < 24 * 60) {
				$m = $ldap_update_users % 60;
				$h = sprintf('*/%s', intdiv($ldap_update_users, 60));
			} else {
				$m = $ldap_update_users % 60;
				$h = intdiv($ldap_update_users, 60) % 24;
			}
			$ce = sprintf('%s %s * * *', $m, $h);
			$ldap_update->cron($ce);
		}
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
