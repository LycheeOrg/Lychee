<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Providers;

use App\Actions\InstallUpdate\CheckUpdate;
use App\Assets\ArrayToTextTable;
use App\Assets\Helpers;
use App\Assets\SizeVariantGroupedWithRandomSuffixNamingStrategy;
use App\Contracts\Models\AbstractSizeVariantNamingStrategy;
use App\Contracts\Models\SizeVariantFactory;
use App\Exceptions\Internal\LycheeLogicException;
use App\Factories\AlbumFactory;
use App\Factories\OmnipayFactory;
use App\Image\SizeVariantDefaultFactory;
use App\Image\StreamStatFilter;
use App\Metadata\Json\CommitsRequest;
use App\Metadata\Json\UpdateRequest;
use App\Metadata\Versions\FileVersion;
use App\Metadata\Versions\GitHubVersion;
use App\Metadata\Versions\InstalledVersion;
use App\Metadata\Versions\Remote\GitCommits;
use App\Metadata\Versions\Remote\GitTags;
use App\Models\Configs;
use App\Policies\AlbumQueryPolicy;
use App\Policies\PhotoQueryPolicy;
use App\Policies\SettingsPolicy;
use App\Repositories\ConfigManager;
use App\Services\MoneyService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Octane\Events\RequestTerminated;
use Laravel\Octane\Facades\Octane;
use LycheeVerify\Contract\VerifyInterface;
use LycheeVerify\Verify;
use Opcodes\LogViewer\Facades\LogViewer;
use Safe\Exceptions\StreamException;
use function Safe\stream_filter_register;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * Defines which queries to ignore when doing explain.
	 *
	 * @var string[]
	 */
	private array $ignore_log_SQL =
		[
			'information_schema', // Not interesting
			'migrations',

			// We do not want infinite loops
			'EXPLAIN',

			// Way too noisy
			'configs',
		];

	/** @var array<class-string,class-string> */
	public array $singletons =
		[
			Helpers::class => Helpers::class,
			CheckUpdate::class => CheckUpdate::class,
			AlbumFactory::class => AlbumFactory::class,
			OmnipayFactory::class => OmnipayFactory::class,
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

			MoneyService::class => MoneyService::class,
		];

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->registerMacros();
		$this->registerDatabaseOptions();
		$this->registerLoggerAccess();
		$this->registerHttpAndResponseConfiguration();
		$this->registerStreamFilters();
		$this->registerOctaneSettings();
	}

	/**
	 * Register the macros that are used for the requests.
	 *
	 * @return void
	 */
	private function registerMacros(): void
	{
		Request::macro('verify', function (): VerifyInterface {
			if (config('features.populate-request-macros', false) === true) {
				return resolve(Verify::class);
			}

			if (!$this->attributes->has('verify')) {
				throw new LycheeLogicException('request attribute "verify" is not set.');
			}

			$verify = $this->attributes->get('verify');

			if ($verify instanceof VerifyInterface) {
				return $verify;
			}

			throw new LycheeLogicException('request attribute "verify" is set but not an instance of VerifyInterface.');
		});

		Request::macro('configs', function (): ConfigManager {
			if (config('features.populate-request-macros', false) === true) {
				return resolve(ConfigManager::class);
			}

			if (!$this->attributes->has('configs')) {
				throw new LycheeLogicException('request attribute "configs" is not set.');
			}

			$configs = $this->attributes->get('configs');

			if ($configs instanceof ConfigManager) {
				return $configs;
			}

			throw new LycheeLogicException('request attribute "configs" is set but not an instance of ConfigManager.');
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

		$this->app->bind(
			VerifyInterface::class,
			Verify::class
		);
	}

	/**
	 * Register database configuration.
	 *
	 * @return void
	 */
	private function registerDatabaseOptions(): void
	{
		// Prohibits: db:wipe, migrate:fresh, migrate:refresh, and migrate:reset
		DB::prohibitDestructiveCommands(config('app.env', 'production') !== 'dev');
		if (config('database.db_log_sql', false) === true) {
			// @codeCoverageIgnoreStart
			DB::listen(fn ($q) => $this->logSQL($q));
			// @codeCoverageIgnoreEnd
		}

		/**
		 * We enforce strict mode
		 * this has the following effect:
		 * - lazy loading is disabled
		 * - non-fillable attributes on creation of model are not discarded but throw an error
		 * - prevents accessing missing attributes.
		 */
		Model::shouldBeStrict();
	}

	/**
	 * Set up the callable for accessing LogViewer.
	 *
	 * @return void
	 */
	private function registerLoggerAccess(): void
	{
		/**
		 * Set up the Authorization layer for accessing Logs in LogViewer.
		 */
		LogViewer::auth(function ($request) {
			// Allow to bypass when debug is ON and when env is dev
			// At this point, it is no longer our fault if the Lychee admin have their logs publically accessible.
			if (config('app.debug', false) === true && config('app.env', 'production') === 'dev') {
				// @codeCoverageIgnoreStart
				return true;
				// @codeCoverageIgnoreEnd
			}

			// return true to allow viewing the Log Viewer.
			return !Auth::guest() && Gate::check(SettingsPolicy::CAN_SEE_LOGS, Configs::class);
		});
	}

	/**
	 * @codeCoverageIgnore
	 */
	private function logSQL(QueryExecuted $query): void
	{
		// Quick exit
		if (
			Str::contains(request()->getRequestUri(), 'logs', true) ||
			Str::contains($query->sql, $this->ignore_log_SQL)
		) {
			return;
		}

		// if the query is not slow enough, we do not log it. Default is 100ms.
		if ($query->time < config('database.log_sql_min_time', 100)) {
			return;
		}

		// Get message with binding outside.
		$msg = '(' . $query->time . 'ms) ' . $query->sql . ' [' . implode(', ', $query->bindings) . ']';

		// For pgsql and sqlite we log the query and exit early
		if (config('database.default', 'mysql') !== 'mysql' ||
			config('database.explain', false) === false ||
			!Str::contains($query->sql, 'select')
		) {
			Log::debug($msg);

			return;
		}
		// For mysql we perform an explain as this is usually the one being slower...
		$bindings = collect($query->bindings)->map(function ($q) {
			return match (gettype($q)) {
				'NULL' => "''",
				'string' => "'{$q}'",
				'boolean' => $q ? '1' : '0',
				default => $q,
			};
		})->all();

		$sql_with_bindings = Str::replaceArray('?', $bindings, $query->sql);

		$explain = DB::select('EXPLAIN ' . $query->sql, $query->bindings);
		$renderer = new ArrayToTextTable();
		$renderer->setIgnoredKeys(['possible_keys', 'key_len', 'ref']);

		$msg .= PHP_EOL;
		$msg .= Str::repeat('-', 20) . PHP_EOL;
		$msg .= $sql_with_bindings . PHP_EOL;
		$msg .= $renderer->getTable($explain);
		Log::debug($msg);
	}

	/**
	 * We do not want the JSON to be wrapped and we enfore https if configured so.
	 *
	 * @return void
	 */
	private function registerHttpAndResponseConfiguration(): void
	{
		/**
		 * By default resources are wrapping results in a 'data' attribute.
		 * We disable that.
		 */
		JsonResource::withoutWrapping();

		/**
		 * We force URL to HTTPS if requested in .env via APP_FORCE_HTTPS.
		 */
		if (config('features.force_https') === true) {
			// @codeCoverageIgnoreStart
			URL::forceScheme('https');
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * Used to figure out the hash value of a photo for example...
	 *
	 * @return void
	 */
	private function registerStreamFilters(): void
	{
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
	}

	private function registerOctaneSettings(): void
	{
		// Force flush logs after each request in Octane
		if (app()->bound('octane') === false) {
			Log::info('Octane is not bound, skipping Octane specific settings.');

			return;
		}

		// Reset DB connections after each request to prevent timeouts
		Octane::tick('octane-db-ping', fn () => $this->pingDatabaseConnections())
			->seconds(30);

		// Clean up after each request
		Octane::tick('flush-memory', fn () => $this->flushMemory())
			->seconds(100);

		Event::listen(RequestTerminated::class, function (): void {
			// Flush all log handlers
			foreach (Log::getHandlers() as $handler) {
				$handler->close();
			}
		});
	}

	protected function pingDatabaseConnections(): void
	{
		$connections = config('database.connections');

		foreach (array_keys($connections) as $name) {
			try {
				DB::connection($name)->getPdo();
			} catch (\Exception $e) {
				DB::purge($name);
				DB::reconnect($name);
			}
		}
	}

	protected function flushMemory(): void
	{
		if (gc_enabled()) {
			gc_collect_cycles();
		}
	}
}
