<?php

namespace App\Actions\Diagnostics;

use App\Metadata\LycheeVersion;
use App\Models\Configs;
use Config;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Imagick;

class Info
{
	use Line;

	/** @var LycheeVersion */
	private $lycheeVersion;

	/** @var array */
	private $versions;

	public function __construct(LycheeVersion $lycheeVersion)
	{
		$this->lycheeVersion = $lycheeVersion;
		$this->versions = $this->lycheeVersion->get();
	}

	/**
	 * get the basic pieces of information of the Lychee installation
	 * such as version number, commit id, operating system ...
	 *
	 * @return array
	 */
	public function get(): array
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
}
