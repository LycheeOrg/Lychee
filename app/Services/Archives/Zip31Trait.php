<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Services\Archives;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\LycheeLogicException;
use App\Image\Files\BaseMediaFile;
use App\Image\Files\FlysystemFile;
use App\Models\Configs;
use App\Models\Photo;
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
			/** @disregard */
			return new ZipStream(defaultCompressionMethod: $this->deflateLevel === -1 ? ZipMethod::STORE : ZipMethod::DEFLATE,
				defaultDeflateLevel: $this->deflateLevel,
				enableZip64: Configs::getValueAsBool('zip64'),
				defaultEnableZeroHeader: true, sendHttpHeaders: false);
		}

		throw new LycheeLogicException('Unsupported version of maennchen/zipstream-php');
	}

	protected function addFileToZip(ZipStream $zip, string $fileName, FlysystemFile|BaseMediaFile $file, Photo|null $photo): void
	{
		if ($photo === null) {
			/** @disregard */
			$zip->addFileFromStream(fileName: $fileName, stream: $file->read());

			return;
		}

		/** @disregard */
		$zip->addFileFromStream(fileName: $fileName, stream: $file->read(), comment: $photo->title, lastModificationDateTime: $photo->taken_at ?? $photo->created_at);
	}
}