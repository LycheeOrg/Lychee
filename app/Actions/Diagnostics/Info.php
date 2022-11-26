<?php

namespace App\Actions\Diagnostics;

use App\DTO\LycheeChannelInfo;
use App\Facades\Helpers;
use App\Metadata\LycheeVersion;
use App\Models\Configs;
use Carbon\CarbonTimeZone;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Imagick;
use function Safe\ini_get;

class Info extends Diagnostics
{
	private LycheeVersion $lycheeVersion;

	public function __construct(LycheeVersion $lycheeVersion)
	{
		$this->lycheeVersion = $lycheeVersion;
	}

	/**
	 * get the basic pieces of information of the Lychee installation
	 * such as version number, commit id, operating system ...
	 *
	 * @return string[] array of messages
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
			$imagickVersion = \Imagick::getVersion();
		} else {
			// @codeCoverageIgnoreStart
			$imagick = '-';
			// @codeCoverageIgnoreEnd
		}
		if (!isset($imagickVersion, $imagickVersion['versionNumber'])) {
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
					$results = DB::select(DB::raw('select version() as version'));
					$dbver = $results[0]->version;
					break;
				case 'sqlite':
					$dbtype = 'SQLite';
					$results = DB::select(DB::raw('select sqlite_version() as version'));
					$dbver = $results[0]->version;
					break;
				case 'pgsql':
					$dbtype = 'PostgreSQL';
					$results = DB::select(DB::raw('select version() as version'));
					$dbver = $results[0]->version;
					break;
				default:
					$dbtype = DB::getDriverName();
					$results = DB::select(DB::raw('select version() as version'));
					$dbver = $results[0]->version;
					break;
			}
		} catch (QueryException $e) {
			$dbtype = 'Unknown SQL';
			$dbver = 'unknown';
		}

		// @codeCoverageIgnoreEnd

		// Format Lychee Information
		$lycheeChannelInfo = $this->lycheeVersion->getLycheeChannelInfo();
		switch ($lycheeChannelInfo->channelType) {
			case LycheeChannelInfo::RELEASE_CHANNEL:
				$lycheeChannelName = 'release';
				$lycheeInfoString = $lycheeChannelInfo->releaseVersion->toString();
				break;
			case LycheeChannelInfo::GIT_CHANNEL:
				$lycheeChannelName = 'git';
				$lycheeInfoString = $lycheeChannelInfo->gitInfo !== null ? $lycheeChannelInfo->gitInfo->toString() : 'No git data found.';
				break;
			default:
				$lycheeChannelName = 'unknown';
				$lycheeInfoString = 'not available (this indicates an error)';
		}

		// Output system information
		$infos[] = Diagnostics::line('Lychee Version (' . $lycheeChannelName . '):', $lycheeInfoString);
		$infos[] = Diagnostics::line('DB Version:', $this->lycheeVersion->getDBVersion()->toString());
		$infos[] = '';
		$infos[] = Diagnostics::line('composer install:', $this->lycheeVersion->phpUnit ? 'dev' : '--no-dev');
		$infos[] = Diagnostics::line('APP_ENV:', Config::get('app.env')); // check if production
		$infos[] = Diagnostics::line('APP_DEBUG:', Config::get('app.debug') === true ? 'true' : 'false'); // check if debug is on (will help in case of error 500)
		$infos[] = '';
		$infos[] = Diagnostics::line('System:', PHP_OS);
		$infos[] = Diagnostics::line('PHP Version:', phpversion());
		$infos[] = Diagnostics::line('PHP User agent:', ini_get('user_agent'));
		$timeZone = CarbonTimeZone::create();
		$infos[] = Diagnostics::line('Timezone:', ($timeZone !== false ? $timeZone : null)?->getName());
		$infos[] = Diagnostics::line('Max uploaded file size:', ini_get('upload_max_filesize'));
		$infos[] = Diagnostics::line('Max post size:', ini_get('post_max_size'));
		$infos[] = Diagnostics::line('Max execution time: ', ini_get('max_execution_time'));
		$infos[] = Diagnostics::line($dbtype . ' Version:', $dbver);
		$infos[] = '';
		$infos[] = Diagnostics::line('exec() Available:', Helpers::isExecAvailable() ? 'yes' : 'no');
		$infos[] = Diagnostics::line('Imagick Available:', (string) $imagick);
		$infos[] = Diagnostics::line('Imagick Enabled:', $settings['imagick'] ?? 'key not found in settings');
		$infos[] = Diagnostics::line('Imagick Version:', $imagickVersion);
		$infos[] = Diagnostics::line('GD Version:', $gdVersion['GD Version']);

		return $infos;
	}
}
