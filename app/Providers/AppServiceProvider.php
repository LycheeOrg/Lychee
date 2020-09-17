<?php

namespace App\Providers;

use App\ControllerFunctions\Update\Apply as ApplyUpdate;
use App\ControllerFunctions\Update\Check as CheckUpdate;
use App\Image;
use App\Image\ImageHandler;
use App\Metadata\GitHubFunctions;
use App\Metadata\GitRequest;
use App\Metadata\LycheeVersion;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\AlbumsFunctions;
use App\ModelFunctions\ConfigFunctions;
use App\ModelFunctions\PhotoFunctions;
use App\ModelFunctions\SessionFunctions;
use App\ModelFunctions\SymLinkFunctions;
use App\Models\Configs;
use App\SmartAlbums\SmartFactory;
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
		AlbumsFunctions::class => AlbumsFunctions::class,
		ConfigFunctions::class => ConfigFunctions::class,
		SessionFunctions::class => SessionFunctions::class,
		GitRequest::class => GitRequest::class,
		GitHubFunctions::class => GitHubFunctions::class,
		LycheeVersion::class => LycheeVersion::class,
		CheckUpdate::class => CheckUpdate::class,
		ApplyUpdate::class => ApplyUpdate::class,
		SmartFactory::class => SmartFactory::class,
	];

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		if (config('app.db_log_sql', false)) {
			// @codeCoverageIgnoreStart
			/* @noinspection PhpUndefinedClassInspection */
			DB::listen(function ($query) {
				/* @noinspection PhpUndefinedClassInspection */
				Log::info(
					$query->sql,
					$query->bindings,
					$query->time
				);
			});
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton(
			Image\ImageHandlerInterface::class,
			function ($app) {
				$compressionQuality = Configs::get_value(
					'compression_quality',
					90
				);

				return new ImageHandler($compressionQuality);
			}
		);
	}
}
