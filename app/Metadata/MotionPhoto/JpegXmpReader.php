<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Metadata\MotionPhoto;

use function Safe\fread;
use function Safe\ftell;
use function Safe\unpack;

/**
 * Walks the marker segments of a JPEG header, from SOI to SOS, and picks the
 * standard XMP packet (ADR-100-01).
 *
 * Only the header is read: the walk stops at the start-of-scan marker, so
 * the entropy-coded image data and anything appended to the file (gain map,
 * motion photo video) are never touched. Every segment length is checked
 * against the file size before it is trusted.
 */
final class JpegXmpReader
{
	private const SOI = "\xFF\xD8";
	private const MARKER_PREFIX = 0xFF;
	private const SOS = 0xDA;
	private const APP1 = 0xE1;
	private const XMP_NAMESPACE = "http://ns.adobe.com/xap/1.0/\x00";

	/**
	 * @param resource $stream    readable stream positioned at the start of the file
	 * @param int      $file_size size of the file in bytes
	 *
	 * @return JpegHeader|null null if the stream is not a well-formed JPEG header
	 */
	public static function read($stream, int $file_size): ?JpegHeader
	{
		if (self::readBytes($stream, 2) !== self::SOI) {
			return null;
		}

		$xmp = null;
		while (true) {
			$marker = self::readMarker($stream);
			if ($marker === null) {
				return null;
			}
			if ($marker === self::SOS) {
				return new JpegHeader($xmp, ftell($stream) - 2);
			}

			$payload_length = self::readPayloadLength($stream, $file_size);
			if ($payload_length === null) {
				return null;
			}

			$wants_xmp = $marker === self::APP1 && $xmp === null;
			$payload = $wants_xmp ? self::readBytes($stream, $payload_length) : self::skip($stream, $payload_length);
			$xmp = self::xmpOf($payload) ?? $xmp;
		}
	}

	/**
	 * Reads the next marker code, skipping `0xFF` fill bytes.
	 *
	 * @param resource $stream
	 *
	 * @return int|null null at the end of the stream or if no marker prefix follows
	 */
	private static function readMarker($stream): ?int
	{
		if (self::readByte($stream) !== self::MARKER_PREFIX) {
			return null;
		}

		do {
			$code = self::readByte($stream);
		} while ($code === self::MARKER_PREFIX);

		return $code;
	}

	/**
	 * Reads a segment length and returns the length of its payload.
	 *
	 * @param resource $stream
	 *
	 * @return int|null null if the length is invalid or the payload runs past the end of the file
	 */
	private static function readPayloadLength($stream, int $file_size): ?int
	{
		$bytes = self::readBytes($stream, 2);
		if (strlen($bytes) !== 2) {
			return null;
		}

		$payload_length = unpack('n', $bytes)[1] - 2;
		$is_valid = $payload_length >= 0 && ftell($stream) + $payload_length <= $file_size;

		return $is_valid ? $payload_length : null;
	}

	/**
	 * @return string|null the XMP packet if `$payload` is a standard XMP segment payload
	 */
	private static function xmpOf(string $payload): ?string
	{
		return str_starts_with($payload, self::XMP_NAMESPACE) ? substr($payload, strlen(self::XMP_NAMESPACE)) : null;
	}

	/**
	 * @param resource $stream
	 */
	private static function readByte($stream): ?int
	{
		$byte = self::readBytes($stream, 1);

		return $byte === '' ? null : ord($byte);
	}

	/**
	 * @param resource $stream
	 *
	 * @return string fewer than `$length` bytes at the end of the stream
	 */
	private static function readBytes($stream, int $length): string
	{
		return $length === 0 ? '' : fread($stream, $length);
	}

	/**
	 * @param resource $stream
	 *
	 * @return string always empty, so callers can treat a skipped payload like a read one
	 */
	private static function skip($stream, int $length): string
	{
		fseek($stream, $length, SEEK_CUR);

		return '';
	}
}
