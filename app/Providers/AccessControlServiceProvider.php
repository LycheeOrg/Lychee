<?php

namespace App\Providers;

use App\ModelFunctions\SessionFunctions;
use Illuminate\Support\ServiceProvider;

class AccessControlServiceProvider extends ServiceProvider
{
	/**
	 * Register services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind('AccessControl', function () {
			return resolve(SessionFunctions::class);
		});
	}

	/**
	 * Bootstrap services.
	 *
	 * @return void
	 */
	public function boot()
	{
	}
}
