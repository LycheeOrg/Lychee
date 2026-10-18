<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Metadata\MotionPhoto;

use function Safe\fread;

/**
 * Finds the end-of-image marker of a JPEG, so that what follows it (gain
 * map, motion photo video) can be told apart from the compressed image data.
 *
 * Inside entropy-coded data every `0xFF` byte is followed by `0x00` (byte
 * stuffing) or a restart marker, so the first `FF D9` after the start-of-scan
 * marker is the end of the image (Feature 100, FR-100-01).
 */
final class JpegImageEnd
{
	private const EOI = "\xFF\xD9";

	/**
	 * @param resource $stream
	 * @param int      $from      offset of the start-of-scan marker
	 * @param int      $file_size size of the file in bytes
	 *
	 * @return int|null offset just after the EOI marker; null if the image never ends
	 */
	public static function find($stream, int $from, int $file_size): ?int
	{
		fseek($stream, $from);
		$offset = $from;
		$carry = '';
		while ($offset < $file_size) {
			$chunk = fread($stream, FtypBox::CHUNK_SIZE);
			if ($chunk === '') {
				return null;
			}

			// The last byte of the previous chunk is kept, so a marker split
			// across two chunks is still seen whole.
			$buffer = $carry . $chunk;
			$eoi_at = strpos($buffer, self::EOI);
			if ($eoi_at !== false) {
				return $offset - strlen($carry) + $eoi_at + strlen(self::EOI);
			}

			$carry = substr($buffer, -1);
			$offset += strlen($chunk);
		}

		return null;
	}
}
