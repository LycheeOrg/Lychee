<?php

namespace App\Providers;

use App\Configs;
use App\ControllerFunctions\ApplyUpdateFunctions;
use App\ControllerFunctions\ReadAccessFunctions;
use App\Image;
use App\Image\ImageHandler;
use App\Metadata\GitHubFunctions;
use App\Metadata\GitRequest;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\ConfigFunctions;
use App\ModelFunctions\PhotoFunctions;
use App\ModelFunctions\SessionFunctions;
use App\ModelFunctions\SymLinkFunctions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	public $singletons
		= [
			SymLinkFunctions::class => SymLinkFunctions::class,
			PhotoFunctions::class => PhotoFunctions::class,
			AlbumFunctions::class => AlbumFunctions::class,
			ConfigFunctions::class => ConfigFunctions::class,
			SessionFunctions::class => SessionFunctions::class,
			GitRequest::class => GitRequest::class,
			GitHubFunctions::class => GitHubFunctions::class,
			ApplyUpdateFunctions::class => ApplyUpdateFunctions::class,
			ReadAccessFunctions::class => ReadAccessFunctions::class,
		];

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		if (config('app.debug', false)) {
			/* @noinspection PhpUndefinedClassInspection */
			DB::listen(function ($query) {
				/* @noinspection PhpUndefinedClassInspection */
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
		$this->app->singleton(Image\ImageHandlerInterface::class,
			function ($app) {
				$compressionQuality = Configs::get_value('compression_quality',
					90);

				return new ImageHandler($compressionQuality);
			});
	}
}
