<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Unit\Metadata\MotionPhoto;

use App\Metadata\MotionPhoto\JpegHeader;
use App\Metadata\MotionPhoto\JpegXmpReader;
use Tests\AbstractTestCase;

/**
 * Covers SVC-098-01: the JPEG marker walk that finds the standard XMP packet
 * and the start of the image data (Feature 098, S-098-10).
 */
class JpegXmpReaderTest extends AbstractTestCase
{
	private const SOI = "\xFF\xD8";
	private const XMP_NAMESPACE = "http://ns.adobe.com/xap/1.0/\x00";
	private const EXTENDED_XMP_NAMESPACE = "http://ns.adobe.com/xmp/extension/\x00";
	private const XMP = '<x:xmpmeta xmlns:x="adobe:ns:meta/"/>';

	public function testReturnsXmpAndImageDataOffset(): void
	{
		$head = self::SOI .
			self::segment(0xE0, "JFIF\x00\x01\x01") .
			self::segment(0xE1, "Exif\x00\x00II*\x00") .
			self::segment(0xE1, self::XMP_NAMESPACE . self::XMP) .
			self::segment(0xDB, str_repeat("\x01", 65));
		$header = $this->read($head . self::scan());

		self::assertInstanceOf(JpegHeader::class, $header);
		self::assertSame(self::XMP, $header->xmp);
		self::assertSame(strlen($head), $header->image_data_offset);
	}

	public function testXmpIsNullWithoutXmpSegment(): void
	{
		$head = self::SOI . self::segment(0xE1, "Exif\x00\x00II*\x00");
		$header = $this->read($head . self::scan());

		self::assertInstanceOf(JpegHeader::class, $header);
		self::assertNull($header->xmp);
		self::assertSame(strlen($head), $header->image_data_offset);
	}

	public function testExtendedXmpIsNotTheStandardPacket(): void
	{
		$header = $this->read(self::SOI . self::segment(0xE1, self::EXTENDED_XMP_NAMESPACE . self::XMP) . self::scan());

		self::assertNull($header?->xmp);
	}

	public function testFirstStandardPacketWins(): void
	{
		$header = $this->read(self::SOI .
			self::segment(0xE1, self::XMP_NAMESPACE . self::XMP) .
			self::segment(0xE1, self::XMP_NAMESPACE . '<second/>') .
			self::scan());

		self::assertSame(self::XMP, $header?->xmp);
	}

	public function testFillBytesBeforeAMarkerAreSkipped(): void
	{
		$header = $this->read(self::SOI . "\xFF\xFF" . self::segment(0xE1, self::XMP_NAMESPACE . self::XMP) . self::scan());

		self::assertSame(self::XMP, $header?->xmp);
	}

	public function testSegmentsAfterTheImageDataAreNotRead(): void
	{
		$header = $this->read(self::SOI . self::scan() . self::segment(0xE1, self::XMP_NAMESPACE . self::XMP));

		self::assertNull($header?->xmp);
		self::assertSame(2, $header?->image_data_offset);
	}

	public function testNotAJpeg(): void
	{
		self::assertNull($this->read("\x89PNG\r\n\x1A\n" . str_repeat("\x00", 32)));
	}

	public function testEmptyStream(): void
	{
		self::assertNull($this->read(''));
	}

	public function testTruncatedMarker(): void
	{
		self::assertNull($this->read(self::SOI . "\xFF\xE1\x00"));
	}

	public function testMissingImageData(): void
	{
		self::assertNull($this->read(self::SOI . self::segment(0xE1, self::XMP_NAMESPACE . self::XMP)));
	}

	public function testSegmentLengthBeyondTheFile(): void
	{
		self::assertNull($this->read(self::SOI . "\xFF\xE1\x40\x00" . self::XMP_NAMESPACE . self::XMP));
	}

	public function testSegmentLengthBelowTwo(): void
	{
		self::assertNull($this->read(self::SOI . "\xFF\xE1\x00\x01" . self::scan()));
	}

	public function testMarkerWithoutFfPrefix(): void
	{
		self::assertNull($this->read(self::SOI . "\x00\xE1\x00\x04ab" . self::scan()));
	}

	private function read(string $bytes): ?JpegHeader
	{
		$stream = fopen('php://memory', 'r+b');
		fwrite($stream, $bytes);
		rewind($stream);
		try {
			return JpegXmpReader::read($stream, strlen($bytes));
		} finally {
			fclose($stream);
		}
	}

	private static function segment(int $marker, string $payload): string
	{
		return "\xFF" . chr($marker) . pack('n', strlen($payload) + 2) . $payload;
	}

	/**
	 * A start-of-scan segment followed by some entropy-coded bytes and EOI.
	 */
	private static function scan(): string
	{
		return self::segment(0xDA, "\x01\x01\x00\x00\x3F\x00") . "\x12\x34\x56\x78\xFF\xD9";
	}
}
