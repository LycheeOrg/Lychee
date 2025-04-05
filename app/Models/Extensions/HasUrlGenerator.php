<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models\Extensions;

use App\Enum\SizeVariantType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Models\Configs;
use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;

trait HasUrlGenerator
{
	/**
	 * Given a short, path, storage disk and size variant type, we return the URL formatted data.
	 *
	 * @throws \InvalidArgumentException
	 * @throws ConfigurationKeyMissingException
	 * @throws \RuntimeException
	 * @throws EncryptException
	 */
	public static function pathToUrl(string $short_path, string $storage_disk, SizeVariantType $type): string
	{
		if ($type === SizeVariantType::PLACEHOLDER) {
			return 'data:image/webp;base64,' . $short_path;
		}

		$image_disk = Storage::disk($storage_disk);

		/** @disregard P1013 */
		$storage_adapter = $image_disk->getAdapter();
		if ($storage_adapter instanceof AwsS3V3Adapter) {
			// @codeCoverageIgnoreStart
			return self::getAwsUrl($short_path, $storage_disk);
			// @codeCoverageIgnoreEnd
		}

		if (self::shouldNotUseSignedUrl()) {
			return $image_disk->url($short_path);
		}

		if (Configs::getValueAsBool('secure_image_link_enabled')) {
			$short_path = Crypt::encryptString($short_path);
		}

		$temporary_image_link_life_in_seconds = Configs::getValueAsInt('temporary_image_link_life_in_seconds');

		/** @disregard P1013 */
		return URL::temporarySignedRoute('image', now()->addSeconds($temporary_image_link_life_in_seconds), ['path' => $short_path]);
	}

	/**
	 * Return true if :
	 * - image link protection is disabled
	 * - image link protection is enabled but the user is logged in (and protection is disabled for logged in users)
	 * - image link protection is enabled but the user is an admin
	 *
	 * @return bool
	 */
	protected static function shouldNotUseSignedUrl(): bool
	{
		return
			!Configs::getValueAsBool('temporary_image_link_enabled') ||
			(!Configs::getValueAsBool('temporary_image_link_when_logged_in') && Auth::user() !== null) ||
			(!Configs::getValueAsBool('temporary_image_link_when_admin') && Auth::user()?->may_administrate === true);
	}

	/**
	 * Retrieve the tempary url from AWS if possible.
	 *
	 * @codeCoverageIgnore
	 */
	private static function getAwsUrl(string $short_path, string $storage_disk): string
	{
		// In order to allow a grace period, we create a new symbolic link,
		$temporary_image_link_life_in_seconds = Configs::getValueAsInt('temporary_image_link_life_in_seconds');
		$image_disk = Storage::disk($storage_disk);

		// Return the public URL in case the S3 bucket is set to public, otherwise generate a temporary URL
		$visibility = config('filesystems.disks.s3.visibility', 'private');
		if ($visibility === 'public') {
			/** @disregard P1013 */
			return $image_disk->url($short_path);
		}

		/** @disregard P1013 */
		return $image_disk->temporaryUrl($short_path, now()->addSeconds($temporary_image_link_life_in_seconds));
	}
}