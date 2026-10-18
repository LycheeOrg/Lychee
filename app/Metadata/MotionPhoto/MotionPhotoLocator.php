<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Metadata\MotionPhoto;

use App\Image\Files\NativeLocalFile;
use Illuminate\Support\Facades\Log;

/**
 * Finds the video embedded in a Google Motion Photo JPEG (Feature 100,
 * FR-100-01, ADR-100-01).
 *
 * In order:
 *  1. the `Item:Length` of the container item with semantic `MotionPhoto`
 *     (Motion Photo 1.0, Android 11+),
 *  2. the legacy `GCamera:MicroVideoOffset` (Android ≤ 10),
 *  3. if the file declares `GCamera:MotionPhoto="1"` but neither length
 *     points at a video, the first MP4 `ftyp` box after the JPEG header.
 *
 * A declared length is only trusted if an `ftyp` box starts where it says.
 */
final class MotionPhotoLocator
{
	/**
	 * @return int length of the video in bytes, counted from the end of the
	 *             file; `0` if there is none or the file cannot be read
	 */
	public static function locate(NativeLocalFile $file): int
	{
		try {
			return self::locateInStream($file->read(), $file->getFilesize());
		} catch (\Throwable $e) {
			Log::warning(__METHOD__ . ':' . __LINE__ . ' Motion photo detection failed for ' . $file->getBasename() . ': ' . $e->getMessage());

			return 0;
		} finally {
			$file->close();
		}
	}

	/**
	 * @param resource $stream
	 */
	private static function locateInStream($stream, int $file_size): int
	{
		$header = JpegXmpReader::read($stream, $file_size);
		if ($header === null) {
			return 0;
		}

		$xmp = MotionPhotoXmp::fromXml($header->xmp ?? '');
		$declared = self::firstValidLength($stream, $file_size, $header, [$xmp->item_length, $xmp->legacy_offset]);
		if ($declared !== 0 || !$xmp->is_motion_photo) {
			return $declared;
		}

		$video_start = FtypBox::scan($stream, $header->image_data_offset, $file_size);

		return $video_start === null ? 0 : $file_size - $video_start;
	}

	/**
	 * @param resource $stream
	 * @param int[]    $lengths candidate video lengths, `0` meaning "not declared"
	 */
	private static function firstValidLength($stream, int $file_size, JpegHeader $header, array $lengths): int
	{
		foreach ($lengths as $length) {
			$is_after_header = $length > 0 && $length <= $file_size - $header->image_data_offset;
			if ($is_after_header && FtypBox::isAt($stream, $file_size - $length)) {
				return $length;
			}
		}

		return 0;
	}
}
