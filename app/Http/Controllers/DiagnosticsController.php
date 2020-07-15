<?php

/** @noinspection PhpComposerExtensionStubsInspection */

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Configs;
use App\ControllerFunctions\Diagnostics\BasicPermissionCheck;
use App\ControllerFunctions\Diagnostics\ConfigSanityCheck;
use App\ControllerFunctions\Diagnostics\DBSupportCheck;
use App\ControllerFunctions\Diagnostics\GDSupportCheck;
use App\ControllerFunctions\Diagnostics\ImageOptCheck;
use App\ControllerFunctions\Diagnostics\IniSettingsCheck;
use App\ControllerFunctions\Diagnostics\LycheeDBVersionCheck;
use App\ControllerFunctions\Diagnostics\PHPVersionCheck;
use App\ControllerFunctions\Update\Check as CheckUpdate;
use App\Metadata\DiskUsage;
use App\Metadata\LycheeVersion;
use App\ModelFunctions\ConfigFunctions;
use App\ModelFunctions\SessionFunctions;
use Config;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Imagick;

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
	 * Return the padded string to 32.
	 */
	private function line(string $key, string $value)
	{
		return sprintf('%-32s %s', $key, $value);
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

		// @codeCoverageIgnoreStart

		$checks = [];
		$checks[] = new PHPVersionCheck();
		$checks[] = new DBSupportCheck();
		$checks[] = new GDSupportCheck();
		$checks[] = new LycheeDBVersionCheck($this->lycheeVersion, $this->versions);
		$checks[] = new BasicPermissionCheck();
		$checks[] = new IniSettingsCheck();
		$checks[] = new ConfigSanityCheck($this->configFunctions);
		$checks[] = new ImageOptCheck();

		foreach ($checks as $check) {
			$check->check($errors);
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
	public function get_info(): array
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
		if (
			!isset($imagickVersion, $imagickVersion['versionNumber'])
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
		try {
			switch (DB::getDriverName()) {
				case 'mysql':
					$dbtype = 'MySQL';
					$results = DB::select(DB::raw('select version()'));
					$dbver = $results[0]->{'version()'};
					break;
				case 'sqlite':
					$dbtype = 'SQLite';
					$results = DB::select(DB::raw('select sqlite_version()'));
					$dbver = $results[0]->{'sqlite_version()'};
					break;
				case 'pgsql':
					$dbtype = 'PostgreSQL';
					$results = DB::select(DB::raw('select version()'));
					$dbver = $results[0]->{'version'};
					break;
				default:
					$dbtype = DB::getDriverName();
					$results = DB::select(DB::raw('select version()'));
					$dbver = $results[0]->{'version()'};
					break;
			}
		} catch (QueryException $e) {
			$errors[] = 'Error: ' . $e->getMessage();
			$dbtype = 'Unknown SQL';
			$dbver = 'unknown';
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
		$infos[] = $this->line('Max uploaded file size:', ini_get('upload_max_filesize'));
		$infos[] = $this->line('Max post size:', ini_get('post_max_size'));
		$infos[] = $this->line($dbtype . ' Version:', $dbver);
		$infos[] = '';
		$infos[] = $this->line('Imagick:', $imagick);
		$infos[] = $this->line('Imagick Active:', $settings['imagick'] ?? 'key not found in settings');
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

		try {
			// Load settings
			$settings = $this->configFunctions->min_info();
			foreach ($settings as $key => $value) {
				if (!is_array($value)) {
					$configs[] = $this->line($key . ':', $value);
				}
			}
		} catch (QueryException $e) {
			$configs[] = 'Error: ' . $e->getMessage();
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
