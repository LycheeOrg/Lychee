<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Image;

use App\Contracts\Image\MediaFile;
use App\Exceptions\MediaFileOperationException;
use App\Image\Files\InMemoryBuffer;
use App\Models\SizeVariant;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\ImageException;
use Safe\Exceptions\StreamException;
use function Safe\imagecreatefromstring;
use function Safe\imagewebp;
use function Safe\rewind;
use function Safe\stream_filter_append;
use function Safe\stream_get_contents;

class PlaceholderEncoder
{
	private const IMAGE_QUALITY = 30;

	/** Character length that the encoded base64 image cannot exceed.
	 *	The initial value 255 is determined by the max characters in size_variants.short_path DB column.
	 *  Since base64 encodes into groups of 4 characters, with padding to fill the final
	 *  group if it is less than 3 bytes, the actual limit can be calculated by:
	 * 	255 - (255 % 4) = 252 characters.
	 */
	private const BASE64_SIZE_LIMIT = 252;

	/** Filesize in bytes that the compressed image cannot exceed.
	 * The limit is calculated using the BASE64_SIZE_LIMIT.
	 * Base64 encodes every 3 bytes into 4 characters.
	 *    - 252 / 4 = 63 groups of four characters
	 *    - Since each group of 4 chars corresponds to 3 bytes of data,
	 *      63 * 3 = 189 bytes of unencoded data.
	 */
	private const COMPRESSION_SIZE_LIMIT = 189;
	private const MAX_COMPRESSION_RETRIES = 3;

	private ?\GdImage $gdImage = null;

	/**
	 * @param SizeVariant $sizeVariant unencoded placeholder size variant
	 *
	 * @return void
	 */
	public function do(SizeVariant $sizeVariant): void
	{
		try {
			$originalFile = $sizeVariant->getFile();
			$workingImage = new InMemoryBuffer();

			$this->createGdImage($sizeVariant->getFile());
			$this->compressImage($this->gdImage, $workingImage);
			$this->encodeBase64Placeholder($workingImage);
			$this->savePlaceholder($workingImage, $sizeVariant);

			// delete original file since we now have no reference to it
			$originalFile->delete();
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException('Failed to encode placeholder to base64', $e);
		}
	}

	/**
	 * Returns a GdImage object from the provided file.
	 *
	 * @param MediaFile $file
	 *
	 * @return void
	 *
	 * @throws FilesystemException
	 * @throws ImageException
	 * @throws StreamException
	 */
	private function createGdImage(MediaFile $file): void
	{
		$inMemoryBuffer = new InMemoryBuffer();

		$originalStream = $file->read();
		if (stream_get_meta_data($originalStream)['seekable']) {
			$inputStream = $originalStream;
		} else {
			// We make an in-memory copy of the provided stream,
			// because we must be able to seek/rewind the stream.
			// For example, a readable stream from a remote location (i.e.
			// a "download" stream) is only forward readable once.
			$inMemoryBuffer->write($originalStream);
			$inputStream = $inMemoryBuffer->read();
		}
		$imgBinary = stream_get_contents($inputStream);

		rewind($inputStream);
		/** @var \GdImage $referenceImage */
		$referenceImage = imagecreatefromstring($imgBinary);
		// webp does not support palette images
		imagepalettetotruecolor($referenceImage);

		$this->gdImage = $referenceImage;
	}

	/**
	 * Compress webp image to acceptable size for DB.
	 *
	 * @param \GdImage       $source source Image
	 * @param InMemoryBuffer $output the file to write to
	 *
	 * @return void
	 *
	 * @throws ImageException
	 * @throws FilesystemException
	 */
	private function compressImage(\GdImage $source, InMemoryBuffer $output): void
	{
		$quality = self::IMAGE_QUALITY;
		$retries = 0;
		// Given a proper placeholder source image (16px x 16px) it should
		// almost always be sufficiently compressed on the first attempt.
		// But just in case it isn't we try to compress again.
		do {
			// ensure buffer is empty before trying to compress again
			$emptyStream = \Safe\fopen('php://temp', 'w+');
			$output->write($emptyStream);
			\Safe\fclose($emptyStream);

			imagewebp($source, $output->stream(), $quality);
			$filesize = \Safe\fstat($output->read())['size'];

			$quality -= 5;
			$retries++;
		} while ($filesize > self::COMPRESSION_SIZE_LIMIT && $retries <= self::MAX_COMPRESSION_RETRIES);
	}

	/**
	 * Encodes provided image file to base64.
	 *
	 * @param InMemoryBuffer $file
	 *
	 * @return void
	 *
	 * @throws StreamException
	 */
	private function encodeBase64Placeholder(InMemoryBuffer $file): void
	{
		$inMemoryBuffer = new InMemoryBuffer();

		stream_filter_append($inMemoryBuffer->read(), 'convert.base64-encode', STREAM_FILTER_WRITE);
		$inMemoryBuffer->write($file->read());

		$file->write($inMemoryBuffer->read());
		$inMemoryBuffer->close();
	}

	/**
	 * Saves base64 string and size to DB.
	 *
	 * @param InMemoryBuffer $file
	 * @param SizeVariant    $sizeVariant
	 *
	 * @return void
	 *
	 * @throws FilesystemException
	 * @throws StreamException
	 */
	private function savePlaceholder(InMemoryBuffer $file, SizeVariant $sizeVariant): void
	{
		$base64Length = \Safe\fstat($file->read())['size'];
		if ($base64Length <= self::BASE64_SIZE_LIMIT) {
			$sizeVariant->filesize = $base64Length;
			$sizeVariant->short_path = stream_get_contents($file->read());
			$sizeVariant->save();
		} else {
			throw new MediaFileOperationException('Encoded image is too large.');
		}
	}
}