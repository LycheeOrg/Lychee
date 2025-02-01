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
use ZipStream\ZipStream;

trait Zip21Trait
{
	/**
	 * @return ZipStream
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	protected function createZip(): ZipStream
	{
		if (InstalledVersions::satisfies(new VersionParser(), 'maennchen/zipstream-php', '^2.1')) {
			$options = new \ZipStream\Option\Archive();
			$options->setContentType('application/octet-stream');
			$options->setDeflateLevel($this->deflateLevel);
			$options->setZeroHeader(true);
			$options->setEnableZip64(Configs::getValueAsBool('zip64'));
			$options->setSendHttpHeaders(false);

			return new ZipStream('archive.zip', $options);
		}

		throw new LycheeLogicException('Unsupported version of maennchen/zipstream-php');
	}

	protected function addFileToZip(ZipStream $zip, string $fileName, FlysystemFile|BaseMediaFile $file, Photo|null $photo): void
	{
		if ($photo === null) {
			$zip->addFileFromStream(name: $fileName, stream: $file->read());

			return;
		}

		$options = new \ZipStream\Option\File();
		$options->setComment($photo->title);
		$options->setTime($photo->taken_at ?? $photo->created_at);
		$zip->addFileFromStream(name: $fileName, stream: $file->read(), options: $options);
	}
}