<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Metadata\Versions;

use App\Contracts\Versions\HasIsRelease;
use App\Contracts\Versions\HasVersion;
use App\DTO\Version;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Models\Configs;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/**
 * InstalledVersion contains the following info:
 * - which version is in the Database
 * - are we downloaded from release page (.git is absent)
 * - are we in dev mode (phpunit is present).
 */
class InstalledVersion implements HasVersion, HasIsRelease
{
	/**
	 * False if we are using a release.
	 * We check if the .git folder is present.
	 */
	private bool $isGit;

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
		$this->isGit = File::exists(base_path('.git'));
		$this->phpUnit = File::exists(base_path('vendor/bin/phpunit'));
	}

	/**
	 * Return true if we are using a Release version of Lychee.
	 */
	public function isRelease(): bool
	{
		return !$this->isGit;
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
		if (!Schema::hasTable('configs')) {
			// @codeCoverageIgnoreStart
			return Version::createFromInt(10000);
			// @codeCoverageIgnoreEnd
		}

		return Version::createFromInt(Configs::getValueAsInt('version'));
	}
}
