<?php

namespace App\Providers;

use App\Actions\InstallUpdate\CheckUpdate;
use App\Assets\Helpers;
use App\Assets\SizeVariantGroupedWithRandomSuffixNamingStrategy;
use App\Contracts\Models\AbstractSizeVariantNamingStrategy;
use App\Contracts\Models\SizeVariantFactory;
use App\Factories\AlbumFactory;
use App\Image\SizeVariantDefaultFactory;
use App\Image\StreamStatFilter;
use App\Metadata\Json\CommitsRequest;
use App\Metadata\Json\UpdateRequest;
use App\Metadata\Versions\FileVersion;
use App\Metadata\Versions\GitHubVersion;
use App\Metadata\Versions\InstalledVersion;
use App\Metadata\Versions\Remote\GitCommits;
use App\Metadata\Versions\Remote\GitTags;
use App\ModelFunctions\SymLinkFunctions;
use App\Models\Configs;
use App\Policies\AlbumQueryPolicy;
use App\Policies\PhotoQueryPolicy;
use App\Policies\SettingsPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;
use Safe\Exceptions\StreamException;
use function Safe\stream_filter_register;

class AppServiceProvider extends ServiceProvider
{
	public array $singletons
	= [
		SymLinkFunctions::class => SymLinkFunctions::class,
		Helpers::class => Helpers::class,
		CheckUpdate::class => CheckUpdate::class,
		AlbumFactory::class => AlbumFactory::class,
		AlbumQueryPolicy::class => AlbumQueryPolicy::class,
		PhotoQueryPolicy::class => PhotoQueryPolicy::class,

		// Versioning
		InstalledVersion::class => InstalledVersion::class,
		GitHubVersion::class => GitHubVersion::class,
		FileVersion::class => FileVersion::class,

		// Json requests.
		CommitsRequest::class => CommitsRequest::class,
		UpdateRequest::class => UpdateRequest::class,

		// JsonParsers
		GitCommits::class => GitCommits::class,
		GitTags::class => GitTags::class,
	];

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		/**
		 * By default resources are wrapping results in a 'data' attribute.
		 * We disable that.
		 */
		JsonResource::withoutWrapping();

		if (config('database.db_log_sql', false) === true) {
			DB::listen(function ($query) {
				$msg = $query->sql . ' [' . implode(', ', $query->bindings) . ']';
				Log::debug($msg);
			});
		}

		try {
			$lang = Configs::getValueAsString('lang');
			app()->setLocale($lang);
		} catch (\Throwable $e) {
			/** log and ignore.
			 * This is necessary so that we can continue:
			 * - if Configs table do not exists (no install),
			 * - if the value does not exists in configs (no install),.
			 */
			logger($e);
		}

		/**
		 * We enforce strict mode
		 * this has the following effect:
		 * - lazy loading is disabled
		 * - non-fillable attributes on creation of model are not discarded but throw an error
		 * - prevents accessing missing attributes.
		 */
		Model::shouldBeStrict();

		try {
			stream_filter_register(
				StreamStatFilter::REGISTERED_NAME,
				StreamStatFilter::class
			);
		} catch (StreamException) {
			// We ignore any error here, because Laravel calls the `boot`
			// method several times and any subsequent attempt to register a
			// filter for the same name anew will fail.
		}

		/**
		 * Set up the Authorization layer for accessing Logs in LogViewer.
		 */
		LogViewer::auth(function ($request) {
			// We must disable unsafe-eval because vue3 used by log-viewer requires it.
			// We only do that in that specific case. It is disabled by default otherwise.
			config(['secure-headers.csp.script-src.unsafe-eval' => true]);

			// return true to allow viewing the Log Viewer.
			return Auth::authenticate() !== null && Gate::check(SettingsPolicy::CAN_SEE_LOGS, Configs::class);
		});
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind('Helpers', function () {
			return resolve(Helpers::class);
		});

		$this->app->bind(
			AbstractSizeVariantNamingStrategy::class,
			SizeVariantGroupedWithRandomSuffixNamingStrategy::class
		);

		$this->app->bind(
			SizeVariantFactory::class,
			SizeVariantDefaultFactory::class
		);
	}
}
