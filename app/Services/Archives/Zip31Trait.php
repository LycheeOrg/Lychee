<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services\Archives;

use App\DTO\ZippablePhoto;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\LycheeLogicException;
use App\Repositories\ConfigManager;
use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use ZipStream\CompressionMethod as ZipMethod;
use ZipStream\ZipStream;

trait Zip31Trait
{
	/**
	 * @return ZipStream
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	protected function createZip(): ZipStream
	{
		if (InstalledVersions::satisfies(new VersionParser(), 'maennchen/zipstream-php', '^3.1')) {
			$config_manager = resolve(ConfigManager::class);

			/** @disregard */
			return new ZipStream(defaultCompressionMethod: $this->deflate_level === -1 ? ZipMethod::STORE : ZipMethod::DEFLATE,
				defaultDeflateLevel: $this->deflate_level,
				enableZip64: $config_manager->getValueAsBool('zip64'),
				defaultEnableZeroHeader: true, sendHttpHeaders: false);
		}

		throw new LycheeLogicException('Unsupported version of maennchen/zipstream-php');
	}

	protected function addFileToZip(
		ZipStream $zip,
		ZippablePhoto $zippable_photo,
	): void {
		if ($zippable_photo->title === null) {
			/** @disregard */
			$zip->addFileFromStream(fileName: $zippable_photo->file_name, stream: $zippable_photo->file->read());

			return;
		}

		/** @disregard */
		$zip->addFileFromStream(fileName: $zippable_photo->file_name, stream: $zippable_photo->file->read(), comment: $zippable_photo->title, lastModificationDateTime: $zippable_photo->last_modification_date_time);
	}
}