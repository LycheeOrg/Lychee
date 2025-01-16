<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models\Extensions;

use App\Enum\SizeVariantType;
use App\Exceptions\ConfigurationException;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Models\Configs;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;

trait HasUrlGenerator
{
	/**
	 * Given a short, path, storage disk and size variant type, we return the URL formatted data.
	 *
	 * @param string          $short_path
	 * @param string          $storage_disk
	 * @param SizeVariantType $type
	 *
	 * @return string|null
	 *
	 * @throws \InvalidArgumentException
	 * @throws ConfigurationKeyMissingException
	 * @throws \RuntimeException
	 * @throws BindingResolutionException
	 * @throws MassAssignmentException
	 * @throws ConfigurationException
	 */
	public static function pathToUrl(string $short_path, string $storage_disk, SizeVariantType $type): string|null
	{
		$imageDisk = Storage::disk($storage_disk);

		if ($type === SizeVariantType::PLACEHOLDER) {
			return 'data:image/webp;base64,' . $short_path;
		}

		if (
			!Configs::getValueAsBool('SL_enable') ||
			(!Configs::getValueAsBool('SL_for_admin') && Auth::user()?->may_administrate === true)
		) {
			/** @disregard P1013 */
			return $imageDisk->url($short_path);
		}

		/** @disregard P1013 */
		$storageAdapter = $imageDisk->getAdapter();
		if ($storageAdapter instanceof AwsS3V3Adapter) {
			// @codeCoverageIgnoreStart
			return self::getAwsUrl($short_path, $storage_disk);
			// @codeCoverageIgnoreEnd
		}

		return null;
	}

	/**
	 * Retrieve the tempary url from AWS if possible.
	 *
	 * @return string
	 *
	 * @codeCoverageIgnore
	 */
	private static function getAwsUrl(string $short_path, string $storage_disk): string
	{
		// In order to allow a grace period, we create a new symbolic link,
		$maxLifetime = Configs::getValueAsInt('SL_life_time_days') * 24 * 60 * 60;
		$imageDisk = Storage::disk($storage_disk);

		// Return the public URL in case the S3 bucket is set to public, otherwise generate a temporary URL
		$visibility = config('filesystems.disks.s3.visibility', 'private');
		if ($visibility === 'public') {
			/** @disregard P1013 */
			return $imageDisk->url($short_path);
		}

		/** @disregard P1013 */
		return $imageDisk->temporaryUrl($short_path, now()->addSeconds($maxLifetime));
	}
}