<?php

namespace App\Providers;

use App\Locale\Lang;
use Illuminate\Support\ServiceProvider;

class LangServiceProvider extends ServiceProvider
{
	/**
	 * Register services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind('lang', function () {
			return resolve(Lang::class);
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
