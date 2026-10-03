<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Metadata\Versions;

use App\DTO\Version;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Metadata\Json\UpdateRequest;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/**
 * FileVersion provides information about the code version.
 * It is the value contained in version.md.
 *
 * Up-to-date is checked against the release data in https://lycheeorg.dev/update.json
 * This part is done via the UpdateRequest class.
 */
class FileVersion
{
	public Version $version;
	public ?Version $remote_version = null;

	/**
	 * Basic contructor.
	 *
	 * @return void
	 *
	 * @throws BindingResolutionException
	 * @throws FileNotFoundException
	 * @throws LycheeInvalidArgumentException
	 */
	public function __construct()
	{
		$this->version = Version::createFromString(
			File::get(base_path('version.md'))
		);
	}

	/**
	 * Fetch the latest release version from the remote update feed.
	 */
	public function hydrate(bool $with_remote = true, bool $use_cache = true): void
	{
		if ($with_remote && Schema::hasTable('configs')) {
			$update_request = resolve(UpdateRequest::class);
			$json = $update_request->get_json($use_cache);

			if ($json !== null) {
				$this->remote_version = Version::createFromString($json->lychee->version);
			}
		}
	}

	/**
	 * Version of the code (version.md).
	 */
	public function getVersion(): Version
	{
		return $this->version;
	}

	/**
	 * True unless the remote feed announces a newer release.
	 */
	public function isUpToDate(): bool
	{
		return $this->remote_version === null || $this->remote_version->toInteger() <= $this->version->toInteger();
	}
}