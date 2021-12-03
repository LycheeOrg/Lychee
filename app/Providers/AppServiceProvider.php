<?php

namespace App\Providers;

use App\Actions\AlbumAuthorisationProvider;
use App\Actions\PhotoAuthorisationProvider;
use App\Actions\Update\Apply as ApplyUpdate;
use App\Actions\Update\Check as CheckUpdate;
use App\Assets\Helpers;
use App\Assets\SizeVariantLegacyNamingStrategy;
use App\Contracts\SizeVariantFactory;
use App\Contracts\SizeVariantNamingStrategy;
use App\Factories\AlbumFactory;
use App\Factories\LangFactory;
use App\Image;
use App\Image\ImageHandler;
use App\Image\SizeVariantDefaultFactory;
use App\Locale\Lang;
use App\Metadata\GitHubFunctions;
use App\Metadata\GitRequest;
use App\Metadata\LycheeVersion;
use App\ModelFunctions\ConfigFunctions;
use App\ModelFunctions\SessionFunctions;
use App\ModelFunctions\SymLinkFunctions;
use App\Models\Configs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	public array $singletons
	= [
		SymLinkFunctions::class => SymLinkFunctions::class,
		ConfigFunctions::class => ConfigFunctions::class,
		LangFactory::class => LangFactory::class,
		Lang::class => Lang::class,
		Helpers::class => Helpers::class,
		SessionFunctions::class => SessionFunctions::class,
		GitRequest::class => GitRequest::class,
		GitHubFunctions::class => GitHubFunctions::class,
		LycheeVersion::class => LycheeVersion::class,
		CheckUpdate::class => CheckUpdate::class,
		ApplyUpdate::class => ApplyUpdate::class,
		AlbumFactory::class => AlbumFactory::class,
		AlbumAuthorisationProvider::class => AlbumAuthorisationProvider::class,
		PhotoAuthorisationProvider::class => PhotoAuthorisationProvider::class,
	];

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		if (config('app.db_log_sql', false)) {
			DB::listen(function ($query) {
				$msg = $query->sql . ' [' . implode(', ', $query->bindings) . ']';
				Log::info($msg);
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

		$this->app->bind('AccessControl', function () {
			return resolve(SessionFunctions::class);
		});

		$this->app->bind('lang', function () {
			return resolve(Lang::class);
		});

		$this->app->bind('Helpers', function () {
			return resolve(Helpers::class);
		});

		$this->app->bind(
			SizeVariantNamingStrategy::class,
			SizeVariantLegacyNamingStrategy::class
		);

		$this->app->bind(
			SizeVariantFactory::class,
			SizeVariantDefaultFactory::class
		);
	}
}
