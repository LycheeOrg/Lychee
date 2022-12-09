<?php

namespace App\Metadata\Versions;

use App\Contracts\Versions\HasVersion;
use App\Contracts\Versions\VersionControl;
use App\DTO\Version;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Metadata\Json\UpdateRequest;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;

class FileVersion implements VersionControl, HasVersion
{
	public Version $version;
	public ?Version $remoteVersion = null;

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
	 * {@inheritDoc}
	 */
	public function hydrate(bool $withRemote = true, bool $useCache = true): void
	{
		if ($withRemote) {
			$updateReques = resolve(UpdateRequest::class);
			$json = $updateReques->get_json($useCache);

			if ($json !== null) {
				$this->remoteVersion = Version::createFromString($json->lychee->version);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getVersion(): Version
	{
		return $this->version;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isUpToDate(): bool
	{
		return $this->remoteVersion === null || $this->remoteVersion->toInteger() <= $this->version->toInteger();
	}
}