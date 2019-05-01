<?php

namespace App\Providers;

use App\Configs;
use App\ControllerFunctions\UpdateFunctions;
use App\Image;
use App\Image\ImageHandler;
use App\Metadata\GitHubFunctions;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\ConfigFunctions;
use App\ModelFunctions\PhotoFunctions;
use App\ModelFunctions\SessionFunctions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	public $singletons = [
		AlbumFunctions::class   => AlbumFunctions::class,
		PhotoFunctions::class   => PhotoFunctions::class,
		ConfigFunctions::class  => ConfigFunctions::class,
		SessionFunctions::class => SessionFunctions::class,
		GitHubFunctions::class  => GitHubFunctions::class,
		UpdateFunctions::class  => UpdateFunctions::class
	];



	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		if (config('app.debug', false)) {
			/** @noinspection PhpUndefinedClassInspection */
			DB::listen(function ($query) {
				/** @noinspection PhpUndefinedClassInspection */
				Log::info(
					$query->sql,
					$query->bindings,
					$query->time
				);
			});
		}
	}



	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton(Image\ImageHandlerInterface::class, function ($app) {
			$compressionQuality = Configs::get_value('compression_quality', 90);
			return new ImageHandler($compressionQuality);
		});
	}
}
