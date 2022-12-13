<?php

namespace App\Metadata\Versions;

use App\Contracts\Versions\HasVersion;
use App\DTO\Version;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Models\Configs;
use Illuminate\Support\Facades\File;

class InstalledVersion implements HasVersion
{
	/**
	 * True if we are using a release.
	 * We check if the .git folder is present.
	 */
	private bool $isRelease;

	/**
	 * true if phpunit is present in vendor/bin/
	 * We use this to determine if composer install or composer install --no-dev was used.
	 */
	private bool $phpUnit;

	/**
	 * Base constructor.
	 */
	public function __construct()
	{
		$this->isRelease = !File::exists(base_path('.git'));
		$this->phpUnit = File::exists(base_path('vendor/bin/phpunit'));
	}

	/**
	 * Return true if we are using a Release version of Lychee.
	 */
	public function isRelease(): bool
	{
		return $this->isRelease;
	}

	/**
	 * Return true of the dev dependencies are installed.
	 */
	public function isDev(): bool
	{
		return $this->phpUnit;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function getVersion(): Version
	{
		return Version::createFromInt(Configs::getValueAsInt('version'));
	}
}
