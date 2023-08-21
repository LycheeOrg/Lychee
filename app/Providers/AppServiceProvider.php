<?php

namespace App\Providers;

use App\Actions\InstallUpdate\CheckUpdate;
use App\Assets\ArrayToTextTable;
use App\Assets\Helpers;
use App\Assets\SizeVariantGroupedWithRandomSuffixNamingStrategy;
use App\Contracts\Models\AbstractSizeVariantNamingStrategy;
use App\Contracts\Models\SizeVariantFactory;
use App\Factories\AlbumFactory;
use App\Image\SizeVariantDefaultFactory;
use App\Image\StreamStatFilter;
use App\Livewire\Synth\AlbumSynth;
use App\Livewire\Synth\PhotoSynth;
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
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Opcodes\LogViewer\Facades\LogViewer;
use Safe\Exceptions\StreamException;
use function Safe\stream_filter_register;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * Defines which queries to ignore when doing explain.
	 *
	 * @var array<int,string>
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

	public array $singletons =
	[
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

	private array $livewireSynth =
	[
		AlbumSynth::class,
		PhotoSynth::class,
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
			DB::listen(fn ($q) => $this->logSQL($q));
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
			// Allow to bypass when debug is ON and when env is dev
			// At this point, it is no longer our fault if the Lychee admin have their logs publically accessible.
			if (config('app.debug', false) === true && config('app.env', 'production') === 'dev') {
				return true;
			}

			// return true to allow viewing the Log Viewer.
			return Auth::authenticate() !== null && Gate::check(SettingsPolicy::CAN_SEE_LOGS, Configs::class);
		});

		foreach ($this->livewireSynth as $synth) {
			Livewire::propertySynthesizer($synth);
		}
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

	private function logSQL(QueryExecuted $query): void
	{
		// Quick exit
		if (
			Str::contains(request()->getRequestUri(), 'logs', true) ||
			Str::contains($query->sql, $this->ignore_log_SQL)
		) {
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
				default => $q
			};
		})->all();

		$sql_with_bindings = Str::replaceArray('?', $bindings, $query->sql);

		$explain = DB::select('EXPLAIN ' . $sql_with_bindings);
		$renderer = new ArrayToTextTable();
		$renderer->setIgnoredKeys(['possible_keys', 'key_len', 'ref']);

		$msg .= PHP_EOL;
		$msg .= Str::repeat('-', 20) . PHP_EOL;
		$msg .= $sql_with_bindings . PHP_EOL;
		$msg .= $renderer->getTable($explain);
		Log::debug($msg);
	}
}
