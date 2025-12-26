<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Image\ColourExtractor;

use App\Exceptions\MediaFileOperationException;
use App\Image\Files\FlysystemFile;
use App\Image\Files\InMemoryBuffer;
use App\Services\Image\FileExtensionService;
use Farzai\ColorPalette\Contracts\ImageInterface;
use Farzai\ColorPalette\Images\GdImage;
use Farzai\ColorPalette\Images\ImagickImage;
use GdImage as GlobalGdImage;
use Safe\Exceptions\ImageException;
use function Safe\imagecreatefromstring;
use function Safe\rewind;
use function Safe\stream_get_contents;

class ImageFactoryForColourExtraction
{
	/**
	 * Create an Image Interface for Farzai\ColorPalette from a FlysystemFile.
	 *
	 * @param FlysystemFile $file
	 * @param string        $driver
	 *
	 * @return ImageInterface
	 */
	public static function createFromFile(FlysystemFile $file, string $driver = 'gd'): ImageInterface
	{
		return match ($driver) {
			'gd' => self::createGdImage($file),
			'imagick' => self::createImagickImage($file),
			default => throw new \InvalidArgumentException("Unsupported driver: {$driver}"),
		};
	}

	/**
	 * Create a GD image resource from a FlysystemFile.
	 * This is used by the LeagueExtractor.
	 *
	 * @param FlysystemFile $file
	 *
	 * @return GlobalGdImage
	 */
	public static function createGdResourceFromFile(FlysystemFile $file): \GdImage
	{
		if (!extension_loaded('gd')) {
			// @codeCoverageIgnoreStart
			throw new \RuntimeException('GD extension is not available');
			// @codeCoverageIgnoreEnd
		}

		try {
			$in_memory_buffer = new InMemoryBuffer();
			$input_stream = self::getInputStream($file, $in_memory_buffer);
			$img_binary = stream_get_contents($input_stream);
			rewind($input_stream);

			error_clear_last();
			$img = imagecreatefromstring($img_binary);
			if ($img === false) {
				// @codeCoverageIgnoreStart
				throw ImageException::createFromPhpError();
				// @codeCoverageIgnoreEnd
			}

			return $img;
			// @codeCoverageIgnoreStart
		} catch (\ErrorException $e) {
			throw new \InvalidArgumentException('Failed to create GD image resource');
			// @codeCoverageIgnoreEnd
		} finally {
			$in_memory_buffer->close();
			$file->close();
		}
	}

	private static function createGdImage(FlysystemFile $file): GdImage
	{
		return new GdImage(self::createGdResourceFromFile($file));
	}

	/**
	 * @throws \RuntimeException
	 * @throws \InvalidArgumentException
	 */
	private static function createImagickImage(FlysystemFile $file): ImagickImage
	{
		if (!extension_loaded('imagick')) {
			// @codeCoverageIgnoreStart
			throw new \RuntimeException('Imagick extension is not available');
			// @codeCoverageIgnoreEnd
		}

		try {
			$in_memory_buffer = new InMemoryBuffer();
			$input_stream = self::getInputStream($file, $in_memory_buffer);

			/** @var \Imagick $image */
			$image = new \Imagick();
			$image->readImageFile($input_stream);

			$file_extension_service = resolve(FileExtensionService::class);
			// If the file is a PDF and the user has chosen to support PDF files then try to create an image from the first page
			if ($file->getExtension() === '.pdf' && $file_extension_service->isSupportedOrAcceptedFileExtension($file->getExtension())) {
				// @codeCoverageIgnoreStart
				$image->setIteratorIndex(0);
				// @codeCoverageIgnoreEnd
			}

			return new ImagickImage($image);
			// @codeCoverageIgnoreStart
		} catch (\ImagickException $e) {
			throw new MediaFileOperationException('Failed to load image', $e);
			// @codeCoverageIgnoreEnd
		} finally {
			$in_memory_buffer?->close();
			$file->close();
		}
	}

	/**
	 * @param FlysystemFile $file
	 *
	 * @return resource
	 *
	 * @throws MediaFileOperationException
	 */
	private static function getInputStream(FlysystemFile $file, InMemoryBuffer &$in_memory_buffer)
	{
		$original_stream = $file->read();
		if (stream_get_meta_data($original_stream)['seekable']) {
			$input_stream = $original_stream;
		} else {
			// @codeCoverageIgnoreStart
			// We make an in-memory copy of the provided stream,
			// because we must be able to seek/rewind the stream.
			// For example, a readable stream from a remote location (i.e.
			// a "download" stream) is only forward readable once.
			$in_memory_buffer->write($original_stream);
			$input_stream = $in_memory_buffer->read();
			// @codeCoverageIgnoreEnd
		}

		return $input_stream;
	}
}
