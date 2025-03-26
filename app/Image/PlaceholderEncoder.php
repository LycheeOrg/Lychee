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
use Illuminate\Support\Facades\Log;
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
	 * @param SizeVariant $size_variant unencoded placeholder size variant
	 */
	public function do(SizeVariant $size_variant): void
	{
		try {
			$original_file = $size_variant->getFile();
			$working_image = new InMemoryBuffer();

			$this->createGdImage($size_variant->getFile());
			$this->compressImage($this->gdImage, $working_image);
			$this->encodeBase64Placeholder($working_image);
			$this->savePlaceholder($working_image, $size_variant);

			// delete original file since we now have no reference to it
			$original_file->delete();
		} catch (MediaFileOperationException $e) {
			// Log the error, delete the size variant and continue with the next placeholder
			Log::error($e->getMessage(), [$e]);
			$size_variant->delete();
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException('Failed to encode placeholder to base64', $e);
		}
	}

	/**
	 * Returns a GdImage object from the provided file.
	 *
	 * @throws FilesystemException
	 * @throws ImageException
	 * @throws StreamException
	 */
	private function createGdImage(MediaFile $file): void
	{
		$in_memory_buffer = new InMemoryBuffer();

		$original_stream = $file->read();
		if (stream_get_meta_data($original_stream)['seekable']) {
			$input_stream = $original_stream;
		} else {
			// We make an in-memory copy of the provided stream,
			// because we must be able to seek/rewind the stream.
			// For example, a readable stream from a remote location (i.e.
			// a "download" stream) is only forward readable once.
			$in_memory_buffer->write($original_stream);
			$input_stream = $in_memory_buffer->read();
		}
		$img_binary = stream_get_contents($input_stream);

		rewind($input_stream);
		$reference_image = imagecreatefromstring($img_binary);
		// webp does not support palette images
		imagepalettetotruecolor($reference_image);

		$this->gdImage = $reference_image;
	}

	/**
	 * Compress webp image to acceptable size for DB.
	 *
	 * @param \GdImage       $source source Image
	 * @param InMemoryBuffer $output the file to write to
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
			$empty_stream = \Safe\fopen('php://temp', 'w+');
			$output->write($empty_stream);
			\Safe\fclose($empty_stream);

			imagewebp($source, $output->stream(), $quality);
			$filesize = \Safe\fstat($output->read())['size'];

			$quality -= 5;
			$retries++;
		} while ($filesize > self::COMPRESSION_SIZE_LIMIT && $retries <= self::MAX_COMPRESSION_RETRIES);
	}

	/**
	 * Encodes provided image file to base64.
	 *
	 * @throws StreamException
	 */
	private function encodeBase64Placeholder(InMemoryBuffer $file): void
	{
		$in_memory_buffer = new InMemoryBuffer();

		stream_filter_append($in_memory_buffer->read(), 'convert.base64-encode', STREAM_FILTER_WRITE);
		$in_memory_buffer->write($file->read());

		$file->write($in_memory_buffer->read());
		$in_memory_buffer->close();
	}

	/**
	 * Saves base64 string and size to DB.
	 *
	 * @throws FilesystemException
	 * @throws StreamException
	 */
	private function savePlaceholder(InMemoryBuffer $file, SizeVariant $size_variant): void
	{
		$base64_length = \Safe\fstat($file->read())['size'];
		if ($base64_length <= self::BASE64_SIZE_LIMIT) {
			$size_variant->filesize = $base64_length;
			$size_variant->short_path = stream_get_contents($file->read());
			$size_variant->save();
		} else {
			throw new MediaFileOperationException('Encoded image is too large.');
		}
	}
}