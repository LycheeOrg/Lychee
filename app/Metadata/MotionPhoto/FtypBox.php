<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Metadata\MotionPhoto;

use function Safe\fread;
use function Safe\unpack;

/**
 * Recognises the `ftyp` box that starts every MP4/QuickTime file:
 *
 *     [uint32 box size][ "ftyp" ][major brand ×4] …
 *
 * A match needs a plausible box size and a known video brand, so random
 * bytes in JPEG image data are very unlikely to pass (FR-100-01).
 */
final class FtypBox
{
	public const HEADER_LENGTH = 12;
	public const CHUNK_SIZE = 65536;

	private const TYPE = 'ftyp';
	private const MIN_BOX_SIZE = 8;
	private const MAX_BOX_SIZE = 256;
	private const BRANDS = ['isom', 'iso2', 'iso4', 'iso5', 'iso6', 'mp41', 'mp42', 'avc1', 'qt  '];

	/**
	 * @param resource $stream
	 */
	public static function isAt($stream, int $offset): bool
	{
		fseek($stream, $offset);

		return self::matches(fread($stream, self::HEADER_LENGTH), 0);
	}

	/**
	 * Finds the first `ftyp` box at or after `$from`, reading the stream in
	 * chunks so memory use does not depend on the file size (NFR-100-02).
	 *
	 * @param resource $stream
	 *
	 * @return int|null offset of the box in the stream
	 */
	public static function scan($stream, int $from, int $file_size): ?int
	{
		fseek($stream, $from);
		$offset = $from;
		$carry = '';
		while ($offset < $file_size) {
			$chunk = fread($stream, self::CHUNK_SIZE);
			if ($chunk === '') {
				return null;
			}

			// The last bytes of the previous chunk are kept, so a box header
			// across two chunks is still seen whole.
			$buffer = $carry . $chunk;
			$hit = self::firstMatch($buffer);
			if ($hit !== null) {
				return $offset - strlen($carry) + $hit;
			}

			$carry = substr($buffer, -(self::HEADER_LENGTH - 1));
			$offset += strlen($chunk);
		}

		return null;
	}

	/**
	 * @return int|null position in `$buffer` of the first complete box header
	 */
	private static function firstMatch(string $buffer): ?int
	{
		$type_at = strpos($buffer, self::TYPE, 4);
		while ($type_at !== false && !self::matches($buffer, $type_at - 4)) {
			$type_at = strpos($buffer, self::TYPE, $type_at + 1);
		}

		return $type_at === false ? null : $type_at - 4;
	}

	private static function matches(string $bytes, int $at): bool
	{
		if (strlen($bytes) < $at + self::HEADER_LENGTH) {
			return false;
		}

		$box_size = unpack('N', $bytes, $at)[1];

		return substr($bytes, $at + 4, 4) === self::TYPE &&
			in_array(substr($bytes, $at + 8, 4), self::BRANDS, true) &&
			$box_size >= self::MIN_BOX_SIZE &&
			$box_size <= self::MAX_BOX_SIZE;
	}
}
