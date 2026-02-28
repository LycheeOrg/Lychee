<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services\Image;

use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;
use App\Repositories\ConfigManager;

class FileExtensionService
{
	public const SUPPORTED_PHP_EXIF_IMAGE_TYPES = [
		IMAGETYPE_GIF,
		IMAGETYPE_JPEG,
		IMAGETYPE_PNG,
		IMAGETYPE_WEBP,
		IMAGETYPE_AVIF,
		20, // IMAGETYPE_HEIF;
	];

	public const SUPPORTED_IMAGE_FILE_EXTENSIONS = [
		'.jpg',
		'.jpeg',
		'.png',
		'.gif',
		'.webp',
		'.avif',
		'.heic',
		'.heif',
	];

	public const SUPPORTED_VIDEO_FILE_EXTENSIONS = [
		'.avi',
		'.m4v',
		'.mov',
		'.mp4',
		'.mpg',
		'.ogv',
		'.webm',
		'.wmv',
	];

	public const SUPPORTED_IMAGE_MIME_TYPES = [
		'image/gif',
		'image/jpeg',
		'image/png',
		'image/webp',
		'image/avif',
		'image/heif',
		'image/heic',
	];

	public const SUPPORTED_VIDEO_MIME_TYPES = [
		'video/mp4',
		'video/mpeg',
		'image/x-tga', // mpg; will be corrected by the metadata extractor
		'video/ogg',
		'video/webm',
		'video/quicktime',
		'video/x-ms-asf', // wmv file
		'video/x-ms-wmv', // wmv file
		'video/x-msvideo', // Avi
		'video/x-m4v', // Avi
		'application/octet-stream', // Some mp4 files; will be corrected by the metadata extractor
	];

	public const MIME_TYPES_TO_FILE_EXTENSIONS = [
		'image/gif' => '.gif',
		'image/jpeg' => '.jpg',
		'image/png' => '.png',
		'image/webp' => '.webp',
		'image/avif' => '.avif',
		'image/heif' => '.heif',
		'image/heic' => '.heic',
		'video/mp4' => '.mp4',
		'video/mpeg' => '.mpg',
		'image/x-tga' => '.mpg',
		'video/ogg' => '.ogv',
		'video/webm' => '.webm',
		'video/quicktime' => '.mov',
		'video/x-ms-asf' => '.wmv',
		'video/x-ms-wmv' => '.wmv',
		'video/x-msvideo' => '.avi',
		'video/x-m4v' => '.avi',
		'application/octet-stream' => '.mp4',
	];

	/**
	 * Extensions that can be converted to JPEG via Imagick (with delegates).
	 * Case-insensitive matching — values are lowercase with leading dot.
	 *
	 * @var string[]
	 */
	public const CONVERTIBLE_RAW_EXTENSIONS = [
		'.nef',
		'.cr2',
		'.cr3',
		'.arw',
		'.dng',
		'.orf',
		'.rw2',
		'.raf',
		'.pef',
		'.srw',
		'.nrw',
		'.psd',
		'.heic',
		'.heif',
	];

	/** @var string[] the accepted raw file extensions minus supported extensions */
	protected array $cached_accepted_raw_file_extensions = [];
	private ConfigManager $config_manager;

	public function __construct()
	{
		$this->config_manager = app(ConfigManager::class);
	}

	/**
	 * Checks if the given MIME type designates a supported image type.
	 *
	 * @param string $mime_type the MIME type
	 */
	public function isSupportedImageMimeType(string $mime_type): bool
	{
		return in_array($mime_type, self::SUPPORTED_IMAGE_MIME_TYPES, true);
	}

	/**
	 * Checks if the given MIME type designates a supported video type.
	 *
	 * @param string $mime_type the MIME type
	 */
	public function isSupportedVideoMimeType(string $mime_type): bool
	{
		return in_array($mime_type, self::SUPPORTED_VIDEO_MIME_TYPES, true);
	}

	/**
	 * Checks if the given file extension is a supported image extension.
	 *
	 * @param string $extension the file extension
	 */
	public function isSupportedImageFileExtension(string $extension): bool
	{
		return in_array(strtolower($extension), self::SUPPORTED_IMAGE_FILE_EXTENSIONS, true);
	}

	/**
	 * Checks if the given file extension is a supported image extension.
	 *
	 * @param string $extension the file extension
	 */
	public function isSupportedVideoFileExtension(string $extension): bool
	{
		return in_array(strtolower($extension), self::SUPPORTED_VIDEO_FILE_EXTENSIONS, true);
	}

	/**
	 * Checks if the given file extension is supported.
	 *
	 * @param string $extension the file extension
	 */
	public function isSupportedFileExtension(string $extension): bool
	{
		return
			$this->isSupportedImageFileExtension($extension) ||
			$this->isSupportedVideoFileExtension($extension);
	}

	/**
	 * Returns {@link MediaFile::cached_accepted_raw_file_extensions} and creates it, if necessary.
	 *
	 * @return string[]
	 */
	public function getSanitizedAcceptedRawFileExtensions(): array
	{
		if (count($this->cached_accepted_raw_file_extensions) === 0) {
			$tmp = explode('|', strtolower($this->config_manager->getValueAsString('raw_formats')));

			// We imagick is enabeld, then we can allow PDF files.
			if ($this->config_manager->hasImagick()) {
				$tmp[] = '.pdf';
				// If imagick is enabled, we should also support the raw files.
				$tmp = array_merge($tmp, self::CONVERTIBLE_RAW_EXTENSIONS);
			}

			// Explode may return `false` on error
			// Our supported file extensions always take precedence over any
			// custom configured extension
			$this->cached_accepted_raw_file_extensions = array_diff($tmp, self::SUPPORTED_IMAGE_FILE_EXTENSIONS, self::SUPPORTED_VIDEO_FILE_EXTENSIONS);
		}

		return $this->cached_accepted_raw_file_extensions;
	}

	/**
	 * Checks if the given extension is accepted as raw.
	 *
	 * @param string $extension the file extension
	 */
	public function isAcceptedRawFileExtension(string $extension): bool
	{
		return in_array(
			strtolower($extension),
			$this->getSanitizedAcceptedRawFileExtensions(),
			true
		);
	}

	/**
	 * Check if the given extension is supported or accepted.
	 *
	 * @param string $extension the file extension
	 */
	public function isSupportedOrAcceptedFileExtension(string $extension): bool
	{
		return
			$this->isSupportedFileExtension($extension) ||
			$this->isAcceptedRawFileExtension($extension);
	}

	/**
	 * Asserts that the given extension is supported or accepted.
	 *
	 * @param string $extension the file extension
	 *
	 * @throws MediaFileUnsupportedException
	 */
	public function assertIsSupportedOrAcceptedFileExtension(string $extension): void
	{
		if (!$this->isSupportedOrAcceptedFileExtension($extension)) {
			// @codeCoverageIgnoreStart
			throw new MediaFileUnsupportedException(MediaFileUnsupportedException::DEFAULT_MESSAGE . ' (bad extension: ' . $extension . ')');
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * Check if the given mimetype is supported or accepted.
	 *
	 * @param string $mime_type the file mimetype
	 */
	public function isSupportedMimeType(string $mime_type): bool
	{
		return
			$this->isSupportedImageMimeType($mime_type) ||
			$this->isSupportedVideoMimeType($mime_type);
	}

	/**
	 * Returns the default file extension for the given MIME type or an empty string if there is no default extension.
	 *
	 * @param string $mime_type a MIME type
	 *
	 * @return string the default file extension for the given MIME type
	 */
	public function getDefaultFileExtensionForMimeType(string $mime_type): string
	{
		return self::MIME_TYPES_TO_FILE_EXTENSIONS[strtolower($mime_type)] ?? '';
	}

	/**
	 * Checks if the file is a valid image type acc. to {@link MediaFile::SUPPORTED_PHP_EXIF_IMAGE_TYPES}.
	 *
	 * @return bool true, if the file has a valid EXIF type
	 */
	protected function hasSupportedExifImageType(string $path): bool
	{
		try {
			return in_array(exif_imagetype($path), self::SUPPORTED_PHP_EXIF_IMAGE_TYPES, true);
			// @codeCoverageIgnoreStart
		} catch (\ErrorException|MediaFileOperationException) {
			// `exif_imagetype` emit an engine error E_NOTICE, if it is unable
			// to read enough bytes from the file to determine the image type.
			// This may happen for short "raw" files.
			return false;
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Checks if the file is a supported image.
	 *
	 * @throws MediaFileOperationException
	 */
	public function isSupportedImage(string $path, string $mime_type, string $extension): bool
	{
		return
			$this->isSupportedImageMimeType($mime_type) &&
			$this->isSupportedImageFileExtension($extension) &&
			$this->hasSupportedExifImageType($path);
	}

	/**
	 * Checks if the file is a supported video.
	 *
	 * @throws MediaFileOperationException
	 */
	public function isSupportedVideo(string $mime_type, string $extension): bool
	{
		return
			$this->isSupportedVideoMimeType($mime_type) &&
			$this->isSupportedVideoFileExtension($extension);
	}

	/**
	 * Checks if the file is supported (image or video).
	 *
	 * @return bool true, if the file is supported
	 *
	 * @throws MediaFileOperationException
	 */
	public function isSupported(string $path, string $mime_type, string $extension): bool
	{
		return
			$this->isSupportedImage($path, $mime_type, $extension) ||
			$this->isSupportedVideo($mime_type, $extension);
	}

	/**
	 * Checks if the file is not supported, but an accepted raw media.
	 */
	public function isAcceptedRaw(string $extension): bool
	{
		return in_array(
			strtolower($extension),
			$this->getSanitizedAcceptedRawFileExtensions(),
			true
		);
	}

	/**
	 * Checks if the file is supported or accepted (i.e. image, video or raw).
	 *
	 * @return bool true, if the file is supported or accepted
	 *
	 * @throws MediaFileOperationException
	 */
	public function isSupportedMediaOrAcceptedRaw(string $path, string $mime_type, string $extension): bool
	{
		return $this->isSupported($path, $mime_type, $extension) || $this->isAcceptedRaw($extension);
	}

	/**
	 * Asserts that the file is supported or accepted (i.e. image, video or raw).
	 *
	 * @throws MediaFileUnsupportedException
	 * @throws MediaFileOperationException
	 */
	public function assertIsSupportedMediaOrAcceptedRaw(string $path, string $mime_type, string $extension): void
	{
		if (!$this->isSupportedMediaOrAcceptedRaw($path, $mime_type, $extension)) {
			// @codeCoverageIgnoreStart
			throw new MediaFileUnsupportedException();
			// @codeCoverageIgnoreEnd
		}
	}
}