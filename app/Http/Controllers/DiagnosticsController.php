<?php

/** @noinspection PhpComposerExtensionStubsInspection */

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Configs;
use App\Metadata\DiskUsage;
use App\Metadata\GitHubFunctions;
use App\ModelFunctions\ConfigFunctions;
use App\ModelFunctions\Helpers;
use App\ModelFunctions\SessionFunctions;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Imagick;

class DiagnosticsController extends Controller
{
	/**
	 * @var ConfigFunctions
	 */
	private $configFunctions;

	/**
	 * @var GitHubFunctions
	 */
	private $gitHubFunctions;

	/**
	 * @var SessionFunctions
	 */
	private $sessionFunctions;

	/**
	 * @var DiskUsage
	 */
	private $diskUsage;

	/**
	 * padding for alignment.
	 *
	 * @var int
	 */
	private $pad_length = 27;

	/**
	 * @param ConfigFunctions  $configFunctions
	 * @param GitHubFunctions  $gitHubFunctions
	 * @param SessionFunctions $sessionFunctions
	 * @param DiskUsage        $diskUsage
	 */
	public function __construct(ConfigFunctions $configFunctions, GitHubFunctions $gitHubFunctions, SessionFunctions $sessionFunctions, DiskUsage $diskUsage)
	{
		$this->configFunctions = $configFunctions;
		$this->gitHubFunctions = $gitHubFunctions;
		$this->sessionFunctions = $sessionFunctions;
		$this->diskUsage = $diskUsage;
	}

	public function get_errors()
	{
		// Declare
		$errors = array();

		// PHP Version
		if (floatval(phpversion()) < 7.2) {
			$errors += ['Warning: Upgrade to PHP 7.2 or higher'];
			// on Dec 1 2019 enable this to an error.
//			$errors += ['Error: Upgrade to PHP 7.2 or higher'];
		}
		if (floatval(phpversion()) < 7.3) {
			$errors += ['Info: Latest version of PHP is 7.3'];
			// on November 30 2019 enable this as warning.
//			$errors += ['Warning: Upgrade to PHP 7.3 or higher'];
			// on November 30 2020 enable this as warning.
//			$errors += ['Error: Upgrade to PHP 7.3 or higher'];
		}

		// 32 or 64 bits ?
		if (PHP_INT_MAX == 2147483647) {
			$errors += ['Warning: Using 32 bit PHP, recommended upgrade to 64 bit'];
		}

		// Extensions
		if (!extension_loaded('session')) {
			$errors += ['Error: PHP session extension not activated'];
		}
		if (!extension_loaded('exif')) {
			$errors += ['Error: PHP exif extension not activated'];
		}
		if (!extension_loaded('mbstring')) {
			$errors += ['Error: PHP mbstring extension not activated'];
		}
		if (!extension_loaded('gd')) {
			$errors += ['Error: PHP gd extension not activated'];
		}
		if (!extension_loaded('PDO')) {
			$errors += ['Error: PHP PDO extension not activated'];
		}
		if (!extension_loaded('mysqli') && !DB::getDriverName() == 'pgsql') {
			$errors += ['Error: PHP mysqli extension not activated'];
		}
		if (!extension_loaded('json')) {
			$errors += ['Error: PHP json extension not activated'];
		}
		if (!extension_loaded('zip')) {
			$errors += ['Error: PHP zip extension not activated'];
		}

		// Permissions
		if (Helpers::hasPermissions(Config::get('defines.dirs.LYCHEE_UPLOADS_BIG')) === false) {
			$errors += ['Error: \'uploads/big\' is missing or has insufficient read/write privileges'];
		}
		if (Helpers::hasPermissions(Config::get('defines.dirs.LYCHEE_UPLOADS_MEDIUM')) === false) {
			$errors += ['Error: \'uploads/medium\' is missing or has insufficient read/write privileges'];
		}
		if (Helpers::hasPermissions(Config::get('defines.dirs.LYCHEE_UPLOADS_SMALL')) === false) {
			$errors += ['Error: \'uploads/small\' is missing or has insufficient read/write privileges'];
		}
		if (Helpers::hasPermissions(Config::get('defines.dirs.LYCHEE_UPLOADS_THUMB')) === false) {
			$errors += ['Error: \'uploads/thumb\' is missing or has insufficient read/write privileges'];
		}
		if (Helpers::hasPermissions(Config::get('defines.dirs.LYCHEE_UPLOADS_IMPORT')) === false) {
			$errors += ['Error: \'uploads/import\' is missing or has insufficient read/write privileges'];
		}
		if (Helpers::hasPermissions(Config::get('defines.dirs.LYCHEE_UPLOADS')) === false) {
			$errors += ['Error: \'uploads/\' is missing or has insufficient read/write privileges'];
		}
		if (Helpers::hasPermissions(Config::get('defines.dirs.LYCHEE_DIST') . '/user.css') === false) {
			$errors += ['Warning: \'dist/user.css\' does not exist or has insufficient read/write privileges.'];
			if (Helpers::hasPermissions(Config::get('defines.dirs.LYCHEE_DIST')) === false) {
				$errors += ['Warning: \'dist/\' has insufficient read/write privileges.'];
			}
		}

		// About GD
		if (function_exists('gd_info')) {
			$gdVersion = gd_info();
			if (!$gdVersion['JPEG Support']) {
				$errors += ['Error: PHP gd extension without jpeg support'];
			}
			if (!$gdVersion['PNG Support']) {
				$errors += ['Error: PHP gd extension without png support'];
			}
			if (!$gdVersion['GIF Read Support'] || !$gdVersion['GIF Create Support']) {
				$errors += ['Error: PHP gd extension without full gif support'];
			}
		}

		// Load settings
		$settings = Configs::get();

		// Settings
		if (!isset($settings['username']) || $settings['username'] == '') {
			$errors += ['Error: Username empty or not set in database'];
		}
		if (!isset($settings['password']) || $settings['password'] == '') {
			$errors += ['Error: Password empty or not set in database'];
		}
		if (!isset($settings['sortingPhotos']) || $settings['sortingPhotos'] == '') {
			$errors += ['Error: Wrong property for sortingPhotos in database'];
		}
		if (!isset($settings['sortingAlbums']) || $settings['sortingAlbums'] == '') {
			$errors += ['Error: Wrong property for sortingAlbums in database'];
		}
		if (!isset($settings['imagick']) || $settings['imagick'] == '') {
			$errors += ['Error: No or wrong property for imagick in database'];
		}
		if (!isset($settings['skipDuplicates']) || $settings['skipDuplicates'] == '') {
			$errors += ['Error: No or wrong property for skipDuplicates in database'];
		}
		if (!isset($settings['checkForUpdates']) || ($settings['checkForUpdates'] != '0' && $settings['checkForUpdates'] != '1')) {
			$errors += ['Error: No or wrong property for checkForUpdates in database'];
		}

		// Check dropboxKey
		if (!$settings['dropboxKey']) {
			$errors += ['Warning: Dropbox import not working. No property for dropboxKey.'];
		}

		// Check php.ini Settings
		if (ini_get('max_execution_time') < 200 && ini_set('upload_max_filesize', '20M') === false) {
			$errors += ['Warning: You may experience problems when uploading a large amount of photos. Take a look in the FAQ for details.'];
		}
		if (empty(ini_get('allow_url_fopen'))) {
			$errors += ['Warning: You may experience problems with the Dropbox- and URL-Import. Edit your php.ini and set allow_url_fopen to 1.'];
		}

		// Check imagick
		if (!extension_loaded('imagick')) {
			$errors += ['Warning: Pictures that are rotated lose their metadata! Please install Imagick to avoid that.'];
		} else {
			if (!$settings['imagick']) {
				$errors += ['Warning: Pictures that are rotated lose their metadata! Please enable Imagick in settings to avoid that.'];
			}
		}

		return $errors;
	}

	/**
	 * @return array
	 */
	public function get_info()
	{
		// Declare
		$infos = array();

		// Load settings
		$settings = Configs::get();

		// Load json (we need to add a try case here
		$json = @file_get_contents(Config::get('defines.path.LYCHEE') . 'public/Lychee-front/package.json');
		if ($json == false) {
			$json = ['version' => '-'];
		} else {
			$json = json_decode($json, true);
		}

		// Load Git info
		$git_info = $this->gitHubFunctions->get_info();

		// About Imagick version
		$imagick = extension_loaded('imagick');
		if ($imagick === true) {
			$imagickVersion = @Imagick::getVersion();
		} else {
			$imagick = '-';
		}
		if (!isset($imagickVersion, $imagickVersion['versionNumber']) || $imagickVersion === '') {
			$imagickVersion = '-';
		} else {
			$imagickVersion = $imagickVersion['versionNumber'];
		}

		// About GD version
		if (function_exists('gd_info')) {
			$gdVersion = gd_info();
		} else {
			$gdVersion = ['GD Version' => '-'];
		}

		// About SQL version
		if (DB::getDriverName() == 'mysql') {
			$results = DB::select(DB::raw('select version()'));
			$dbver = $results[0]->{'version()'};
			$dbtype = 'MySQL';
		} else {
			if (DB::getDriverName() == 'sqlite') {
				$results = DB::select(DB::raw('select sqlite_version()'));
				$dbver = $results[0]->{'sqlite_version()'};
				$dbtype = 'SQLite';
			} else {
				if (DB::getDriverName() == 'pgsql') {
					$results = DB::select(DB::raw('select version()'));
					$dbver = $results[0]->{'version'};
					$dbtype = 'PostgreSQL';
				} else {
					try {
						$results = DB::select(DB::raw('select version()'));
						$dbver = $results[0]->{'version()'};
					} catch (Exception $e) {
						$dbver = 'unknown';
					}
					$dbtype = DB::getDriverName();
				}
			}
		}

		// Output system information
		$infos[] = str_pad('Lychee-front Version:', $this->pad_length) . $json['version'];
		$infos[] = str_pad('Lychee Version (git):', $this->pad_length) . $git_info;
		$infos[] = str_pad('DB Version:', $this->pad_length) . $settings['version'];
		$infos[] = str_pad('System:', $this->pad_length) . PHP_OS;
		$infos[] = str_pad('PHP Version:', $this->pad_length) . floatval(phpversion());
		$infos[] = str_pad($dbtype . ' Version:', $this->pad_length) . $dbver;
		$infos[] = '';
		$infos[] = str_pad('Lychee total space:', $this->pad_length) . $this->diskUsage->get_lychee_space();
		$infos[] = str_pad('Upload folder space:', $this->pad_length) . $this->diskUsage->get_lychee_upload_space();
		$infos[] = str_pad('System total space:', $this->pad_length) . $this->diskUsage->get_total_space();
		$infos[] = str_pad('System free space:', $this->pad_length) . $this->diskUsage->get_free_space() . ' (' . $this->diskUsage->get_free_percent() . ')';
		$infos[] = '';
		$infos[] = str_pad('Imagick:', $this->pad_length) . $imagick;
		$infos[] = str_pad('Imagick Active:', $this->pad_length) . $settings['imagick'];
		$infos[] = str_pad('Imagick Version:', $this->pad_length) . $imagickVersion;
		$infos[] = str_pad('GD Version:', $this->pad_length) . $gdVersion['GD Version'];

		return $infos;
	}

	public function get_config()
	{
		// Declare
		$configs = array();

		// Load settings
		$settings = $this->configFunctions->min_info();
		foreach ($settings as $key => $value) {
			if (!is_array($value)) {
				$configs[] = str_pad($key . ':', $this->pad_length - 1) . ' ' . $value;
			}
		}

		return $configs;
	}

	public function get()
	{
		$errors = $this->get_errors();
		$infos = ['You must be logged to see this.'];
		$configs = ['You must be logged to see this.'];
		if ($this->sessionFunctions->is_admin()) {
			$infos = $this->get_info();
			$configs = $this->get_config();
		}

		$update = true;
		$update &= Configs::get_value('allow_online_git_pull', '0') == '1';
		$update &= function_exists('exec');
		$update &= is_executable('../.git');

		try {
			$update &= !$this->gitHubFunctions->is_up_to_date();
		} catch (Exception $e) {
			$update = false;
		}

		return [
			'errors' => $errors,
			'infos' => $infos,
			'configs' => $configs,
			'update' => $update,
		];
	}

	public function show()
	{
		$errors = $this->get_errors();
		$infos = ['You must be logged to see this.'];
		$configs = ['You must be logged to see this.'];
		if ($this->sessionFunctions->is_admin()) {
			$infos = $this->get_info();
			$configs = $this->get_config();
		}

		// Show separator
		return view('diagnostics', [
			'errors' => $errors,
			'infos' => $infos,
			'configs' => $configs,
		]);
	}
}
