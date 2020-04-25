<?php

/** @noinspection PhpComposerExtensionStubsInspection */

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Configs;
use App\ControllerFunctions\Update\Check as CheckUpdate;
use App\Metadata\DiskUsage;
use App\Metadata\LycheeVersion;
use App\ModelFunctions\ConfigFunctions;
use App\ModelFunctions\Helpers;
use App\ModelFunctions\SessionFunctions;
use Config;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Imagick;
use Storage;

class DiagnosticsController extends Controller
{
	/**
	 * @var ConfigFunctions
	 */
	private $configFunctions;

	/**
	 * @var LycheeVersion
	 */
	private $lycheeVersion;

	/**
	 * @var SessionFunctions
	 */
	private $sessionFunctions;

	/**
	 * @var DiskUsage
	 */
	private $diskUsage;

	/**
	 * @var CheckUpdate
	 */
	private $checkUpdate;

	/**
	 * @var array
	 */
	private $versions;

	/**
	 * @param ConfigFunctions  $configFunctions
	 * @param LycheeVersion    $lycheeVersion
	 * @param SessionFunctions $sessionFunctions
	 * @param DiskUsage        $diskUsage
	 * @param CheckUpdate      $checkUpdate
	 */
	public function __construct(
		ConfigFunctions $configFunctions,
		LycheeVersion $lycheeVersion,
		SessionFunctions $sessionFunctions,
		DiskUsage $diskUsage,
		CheckUpdate $checkUpdate
	) {
		$this->configFunctions = $configFunctions;
		$this->lycheeVersion = $lycheeVersion;
		$this->sessionFunctions = $sessionFunctions;
		$this->diskUsage = $diskUsage;
		$this->checkUpdate = $checkUpdate;

		$this->versions = $this->lycheeVersion->get();
	}

	/**
	 * Return the padded string to 27.
	 */
	private function line(string $key, string $value)
	{
		return sprintf('%-27s %s', $key, $value);
	}

	/**
	 * Return the list of error which are currently breaking Lychee.
	 *
	 * @return array
	 */
	public function get_errors()
	{
		// Declare
		$errors = [];

		// PHP Version

		// As we cannot test this as those are just raising warnings which we cannot check via Travis.
		// I hereby solemnly  declare this code as covered !
		// @codeCoverageIgnoreStart

		$php_error = 7.2;
		$php_warning = 7.3;
		$php_latest = 7.4;

		// 30 Nov 2019	 => 7.2 = DEPRECATED = ERROR
		// 28 Nov 2019	 => 7.4 = RELEASED   => 7.3 = WARNING
		// 6 Dec 2020	 => 7.3 = DEPRECATED = ERROR
		// 28 Nov 2021	 => 7.4 = DEPRECATED = ERROR

		if (floatval(phpversion()) < $php_latest) {
			$errors[] = 'Info: Latest version of PHP is ' . $php_latest;
		}

		if (floatval(phpversion()) < $php_error) {
			$errors += ['Error: Upgrade to PHP ' . $php_error . ' or higher'];
		}

		if (floatval(phpversion()) < $php_warning) {
			$errors += ['Warning: Upgrade to PHP ' . $php_latest . ' or higher'];
		}

		// 32 or 64 bits ?
		if (PHP_INT_MAX == 2147483647) {
			$errors[]
				= 'Warning: Using 32 bit PHP, recommended upgrade to 64 bit';
		}

		// Extensions
		$extensions = ['session', 'exif', 'mbstring', 'gd', 'PDO', 'json', 'zip'];

		foreach ($extensions as $extension) {
			if (!extension_loaded($extension)) {
				$errors[] = 'Error: PHP ' . $extension . ' extension not activated';
			}
		}
		if (!extension_loaded('mysqli') && !DB::getDriverName() == 'pgsql') {
			$errors[] = 'Error: PHP mysqli extension not activated';
		}

		// Permissions
		$paths = ['big', 'medium', 'small', 'thumb', 'import', ''];

		foreach ($paths as $path) {
			$p = Storage::path($path);
			if (Helpers::hasPermissions($p) === false) {
				$errors[] = "Error: '" . $p . "' is missing or has insufficient read/write privileges";
			}
		}
		$p = Storage::disk('dist')->path('user.css');
		if (Helpers::hasPermissions($p) === false) {
			$errors[] = "Warning: '" . $p . "' does not exist or has insufficient read/write privileges.";
			$p = Storage::disk('dist')->path('');
			if (Helpers::hasPermissions($p) === false) {
				$errors[] = "Warning: '" . $p . "' has insufficient read/write privileges.";
			}
		}

		// About GD
		if (function_exists('gd_info')) {
			$gdVersion = gd_info();
			if (!$gdVersion['JPEG Support']) {
				$errors[] = 'Error: PHP gd extension without jpeg support';
			}
			if (!$gdVersion['PNG Support']) {
				$errors[] = 'Error: PHP gd extension without png support';
			}
			if (!$gdVersion['GIF Read Support']
				|| !$gdVersion['GIF Create Support']
			) {
				$errors[] = 'Error: PHP gd extension without full gif support';
			}
		}

		// Load settings
		$settings = Configs::get();

		$keys_checked = [
			'username', 'password', 'sorting_Photos', 'sorting_Albums',
			'imagick', 'skip_duplicates', 'check_for_updates', 'version',
		];

		foreach ($keys_checked as $key) {
			if (!isset($settings[$key])) {
				$errors[] = 'Error: ' . $key . ' not set in database';
			}
		}

		if ($this->lycheeVersion->isRelease && $this->versions['DB']['version'] < $this->versions['Lychee']['version']) {
			$errors[] = 'Error: Database is behind file versions. Please apply the migration.';
		}

		/*
		 * Sanity check over all the variables
		 */
		$this->configFunctions->sanity($errors);

		// Check dropboxKey
		if (!isset($settings['dropbox_key'])) {
			$errors[]
				= 'Warning: Dropbox import not working. No property for dropbox_key.';
		} elseif ($settings['dropbox_key'] == '') {
			$errors[]
				= 'Warning: Dropbox import not working. dropbox_key is empty.';
		}

		// Check php.ini Settings
		if (ini_get('max_execution_time') < 200
			&& ini_set('upload_max_filesize', '20M') === false
		) {
			$errors[]
				= 'Warning: You may experience problems when uploading a large amount of photos. Take a look in the FAQ for details.';
		}
		if (empty(ini_get('allow_url_fopen'))) {
			$errors[]
				= 'Warning: You may experience problems with the Dropbox- and URL-Import. Edit your php.ini and set allow_url_fopen to 1.';
		}

		// Check imagick
		if (!extension_loaded('imagick')) {
			$errors[]
				= 'Warning: Pictures that are rotated lose their metadata! Please install Imagick to avoid that.';
		} else {
			if (!isset($settings['imagick'])) {
				$errors[]
					= 'Warning: Pictures that are rotated lose their metadata! Please enable Imagick in settings to avoid that.';
			}
		}

		// @codeCoverageIgnoreEnd

		return $errors;
	}

	/**
	 * get the basic pieces of information of the Lychee installation
	 * such as version number, commit id, operating system ...
	 *
	 * @return array
	 */
	public function get_info()
	{
		// Declare
		$infos = [];

		// Load settings
		$settings = Configs::get();

		// About Imagick version
		$imagick = extension_loaded('imagick');
		if ($imagick === true) {
			$imagickVersion = @Imagick::getVersion();
		} else {
			// @codeCoverageIgnoreStart
			$imagick = '-';
			// @codeCoverageIgnoreEnd
		}
		if (!isset($imagickVersion, $imagickVersion['versionNumber'])
			|| $imagickVersion === ''
		) {
			// @codeCoverageIgnoreStart
			$imagickVersion = '-';
		// @codeCoverageIgnoreEnd
		} else {
			$imagickVersion = $imagickVersion['versionNumber'];
		}

		// About GD version
		if (function_exists('gd_info')) {
			$gdVersion = gd_info();
		} else {
			// @codeCoverageIgnoreStart
			$gdVersion = ['GD Version' => '-'];
			// @codeCoverageIgnoreEnd
		}

		// About SQL version
		// @codeCoverageIgnoreStart
		switch (DB::getDriverName()) {
			case 'mysql':
				$results = DB::select(DB::raw('select version()'));
				$dbver = $results[0]->{'version()'};
				$dbtype = 'MySQL';
				break;
			case 'sqlite':
				$results = DB::select(DB::raw('select sqlite_version()'));
				$dbver = $results[0]->{'sqlite_version()'};
				$dbtype = 'SQLite';
				break;
			case 'pgsql':
				$results = DB::select(DB::raw('select version()'));
				$dbver = $results[0]->{'version'};
				$dbtype = 'PostgreSQL';
				break;
			default:
				try {
					$results = DB::select(DB::raw('select version()'));
					$dbver = $results[0]->{'version()'};
				} catch (Exception $e) {
					$dbver = 'unknown';
				}
				$dbtype = DB::getDriverName();
				break;
		}
		// @codeCoverageIgnoreEnd

		// Output system information
		$infos[] = $this->line('Lychee Version (' . $this->versions['channel'] . '):', $this->lycheeVersion->format($this->versions['Lychee']));
		$infos[] = $this->line('DB Version:', $this->versions['DB']['version']);
		$infos[] = '';
		$infos[] = $this->line('composer install:', $this->versions['composer']);
		$infos[] = $this->line('APP_ENV:', Config::get('app.env')); // check if production
		$infos[] = $this->line('APP_DEBUG:', Config::get('app.debug') ? 'true' : 'false'); // check if debug is on (will help in case of error 500)
		$infos[] = '';
		$infos[] = $this->line('System:', PHP_OS);
		$infos[] = $this->line('PHP Version:', floatval(phpversion()));
		$infos[] = $this->line($dbtype . ' Version:', $dbver);
		$infos[] = '';
		$infos[] = $this->line('Imagick:', $imagick);
		$infos[] = $this->line('Imagick Active:', $settings['imagick']);
		$infos[] = $this->line('Imagick Version:', $imagickVersion);
		$infos[] = $this->line('GD Version:', $gdVersion['GD Version']);

		return $infos;
	}

	/**
	 * get space used by Lychee.
	 *
	 * @return array
	 */
	public function get_space(array $infos)
	{
		$infos[] = '';
		$infos[] = $this->line('Lychee total space:', $this->diskUsage->get_lychee_space());
		$infos[] = $this->line('Upload folder space:', $this->diskUsage->get_lychee_upload_space());
		$infos[] = $this->line('System total space:', $this->diskUsage->get_total_space());
		$infos[] = $this->line('System free space:', $this->diskUsage->get_free_space() . ' ('
			. $this->diskUsage->get_free_percent() . ')');

		return $infos;
	}

	/**
	 * Return the config pieces of information of the Lychee installation.
	 * Note that some information such as password and username are hidden.
	 *
	 * @return array
	 */
	public function get_config()
	{
		// Declare
		$configs = [];

		// Load settings
		$settings = $this->configFunctions->min_info();
		foreach ($settings as $key => $value) {
			if (!is_array($value)) {
				$configs[] = $this->line($key . ':', $value);
			}
		}

		return $configs;
	}

	/**
	 * Return the requested information.
	 *
	 * @return array
	 */
	private function get_data()
	{
		$errors = $this->get_errors();
		if ($this->sessionFunctions->is_admin()) {
			$infos = $this->get_info();
			$configs = $this->get_config();
		} else {
			$infos = ['You must be logged to see this.'];
			$configs = ['You must be logged to see this.'];
		}

		return [
			'errors' => $errors,
			'infos' => $infos,
			'configs' => $configs,
		];
	}

	/**
	 * This function return the Diagnostic data as an JSON array.
	 * should be used for AJAX request.
	 *
	 * @return array
	 */
	public function get()
	{
		$ret = $this->get_data();
		$ret['update'] = $this->checkUpdate->getCode();

		return $ret;
	}

	/**
	 * Return the diagnostic information as a page.
	 *
	 * @return View
	 */
	public function show()
	{
		return view('diagnostics', $this->get_data());
	}

	/**
	 * Return the size used by Lychee.
	 * We now separate this from the initial get() call as this is quite time consuming.
	 *
	 * @return array
	 */
	public function get_size()
	{
		$infos = ['You must be logged to see this.'];
		if ($this->sessionFunctions->is_admin()) {
			$infos = $this->get_space([]);
		}

		return $infos;
	}
}
