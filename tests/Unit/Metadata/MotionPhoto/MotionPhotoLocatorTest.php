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

use App\Image\Files\NativeLocalFile;
use App\Metadata\MotionPhoto\MotionPhotoLocator;
use Illuminate\Support\Facades\Log;
use Tests\AbstractTestCase;
use Tests\Constants\TestConstants;

/**
 * Covers SVC-100-03 / FR-100-01: declared lengths checked against an MP4
 * `ftyp` box, and the scan fallback (Feature 100, S-100-01, S-100-03,
 * S-100-04, S-100-05, S-100-08, S-100-10, NFR-100-02).
 */
class MotionPhotoLocatorTest extends AbstractTestCase
{
	private const CHUNK_SIZE = 65536;

	/** @var string[] */
	private array $files = [];

	protected function tearDown(): void
	{
		array_map(fn (string $path) => @unlink($path), $this->files);
		parent::tearDown();
	}

	public function testDeclaredItemLength(): void
	{
		$video = self::mp4(5000);
		$file = $this->file(self::jpeg(self::motionPhoto(strlen($video))) . $video);

		self::assertSame(5000, MotionPhotoLocator::locate($file));
	}

	public function testUltraHdrLayoutSkipsTheGainMap(): void
	{
		$video = self::mp4(5000);
		$gain_map = self::jpeg('');
		$file = $this->file(self::jpeg(self::motionPhoto(strlen($video))) . $gain_map . $video);

		self::assertSame(5000, MotionPhotoLocator::locate($file));
	}

	public function testDeclaredLegacyOffset(): void
	{
		$video = self::mp4(3000);
		$file = $this->file(self::jpeg('GCamera:MicroVideo="1" GCamera:MicroVideoOffset="3000"') . $video);

		self::assertSame(3000, MotionPhotoLocator::locate($file));
	}

	public function testWrongDeclaredLengthFallsBackToTheScan(): void
	{
		$video = self::mp4(5000);
		$file = $this->file(self::jpeg(self::motionPhoto(5007)) . $video);

		self::assertSame(5000, MotionPhotoLocator::locate($file));
	}

	public function testWrongLegacyOffsetIsNotScanned(): void
	{
		$video = self::mp4(3000);
		$file = $this->file(self::jpeg('GCamera:MicroVideo="1" GCamera:MicroVideoOffset="3333"') . $video);

		self::assertSame(0, MotionPhotoLocator::locate($file));
	}

	public function testMissingVideoItemIsScanned(): void
	{
		$video = self::mp4(4000);
		$file = $this->file(self::jpeg('GCamera:MotionPhoto="1"') . $video);

		self::assertSame(4000, MotionPhotoLocator::locate($file));
	}

	public function testScanMiss(): void
	{
		$file = $this->file(self::jpeg('GCamera:MotionPhoto="1"') . str_repeat("\x00", 4000));

		self::assertSame(0, MotionPhotoLocator::locate($file));
	}

	public function testScanRejectsUnknownBrands(): void
	{
		$file = $this->file(self::jpeg('GCamera:MotionPhoto="1"') . self::mp4(4000, 'heic'));

		self::assertSame(0, MotionPhotoLocator::locate($file));
	}

	public function testScanRejectsImplausibleBoxSizes(): void
	{
		$file = $this->file(self::jpeg('GCamera:MotionPhoto="1"') . self::mp4(4000, 'isom', 4096));

		self::assertSame(0, MotionPhotoLocator::locate($file));
	}

	public function testScanFindsABoxAcrossAChunkBoundary(): void
	{
		$video = self::mp4(4000);
		$file = $this->file(self::jpeg('GCamera:MotionPhoto="1"', self::CHUNK_SIZE - 6) . $video);

		self::assertSame(4000, MotionPhotoLocator::locate($file));
	}

	public function testPlainJpegWithTrailingVideoIsNotAMotionPhoto(): void
	{
		$file = $this->file(self::jpeg('') . self::mp4(4000));

		self::assertSame(0, MotionPhotoLocator::locate($file));
	}

	public function testLengthOfTheWholeFileIsRejected(): void
	{
		$video = self::mp4(4000);
		$bytes = self::jpeg(self::motionPhoto(999999)) . $video;
		$file = $this->file($bytes);

		self::assertSame(4000, MotionPhotoLocator::locate($file), 'falls back to the scan');
	}

	public function testNotAJpeg(): void
	{
		$file = $this->file(self::mp4(4000));

		self::assertSame(0, MotionPhotoLocator::locate($file));
	}

	public function testUnreadableFileReturnsZeroAndLogs(): void
	{
		Log::spy();

		self::assertSame(0, MotionPhotoLocator::locate(new NativeLocalFile('/nonexistent/' . uniqid() . '.jpg')));
		Log::shouldHaveReceived('warning')->once();
	}

	public function testPixelSample(): void
	{
		$file = new NativeLocalFile(base_path(TestConstants::SAMPLE_FILE_GMP_PIXEL_IMAGE));

		self::assertSame(3725212, MotionPhotoLocator::locate($file));
	}

	public function testSyntheticSamples(): void
	{
		self::assertSame(10705, MotionPhotoLocator::locate(new NativeLocalFile(base_path(TestConstants::SAMPLE_FILE_GMP_V1_IMAGE))));
		self::assertSame(10705, MotionPhotoLocator::locate(new NativeLocalFile(base_path(TestConstants::SAMPLE_FILE_GMP_ULTRAHDR_IMAGE))));
	}

	public function testLegacySample(): void
	{
		$file = new NativeLocalFile(base_path(TestConstants::SAMPLE_FILE_GMP_IMAGE));

		self::assertSame(4495341, MotionPhotoLocator::locate($file));
	}

	public function testScanUsesConstantMemory(): void
	{
		$video = self::mp4(4000);
		$file = $this->file(self::jpeg('GCamera:MotionPhoto="1"', 20 * 1024 * 1024) . $video);
		gc_collect_cycles();
		memory_reset_peak_usage();
		$before = memory_get_usage();

		self::assertSame(4000, MotionPhotoLocator::locate($file));
		self::assertLessThan(2 * 1024 * 1024, memory_get_peak_usage() - $before);
	}

	private function file(string $bytes): NativeLocalFile
	{
		$path = tempnam(sys_get_temp_dir(), 'lychee-mp-') . '.jpg';
		$this->files[] = $path;
		$this->files[] = substr($path, 0, -4);
		file_put_contents($path, $bytes);

		return new NativeLocalFile($path);
	}

	private static function motionPhoto(int $video_length): string
	{
		return 'GCamera:MotionPhoto="1">' .
			'<Container:Directory><rdf:Seq>' .
			'<rdf:li rdf:parseType="Resource"><Container:Item Item:Mime="image/jpeg" Item:Semantic="Primary"/></rdf:li>' .
			'<rdf:li rdf:parseType="Resource"><Container:Item Item:Mime="video/mp4" Item:Semantic="MotionPhoto" Item:Length="' . $video_length . '"/></rdf:li>' .
			'</rdf:Seq></Container:Directory></rdf:Description><rdf:Description';
	}

	/**
	 * A JPEG whose XMP carries `$camera` (attributes, optionally closing the
	 * description early to add a container directory), and whose image data
	 * spans `$image_bytes` bytes from the SOS marker to the end of EOI.
	 */
	private static function jpeg(string $camera, int $image_bytes = 64): string
	{
		$xmp = $camera === '' ? '' : "http://ns.adobe.com/xap/1.0/\x00" .
			'<x:xmpmeta xmlns:x="adobe:ns:meta/"><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">' .
			'<rdf:Description xmlns:GCamera="http://ns.google.com/photos/1.0/camera/" xmlns:Container="http://ns.google.com/photos/1.0/container/" xmlns:Item="http://ns.google.com/photos/1.0/container/item/" ' .
			$camera . '/></rdf:RDF></x:xmpmeta>';
		$app1 = $xmp === '' ? '' : "\xFF\xE1" . pack('n', strlen($xmp) + 2) . $xmp;
		$sos = "\xFF\xDA\x00\x08\x01\x01\x00\x00\x3F\x00";

		return "\xFF\xD8" . $app1 . $sos . str_repeat("\x12\x34", intdiv($image_bytes - 12, 2)) . "\xFF\xD9";
	}

	private static function mp4(int $length, string $brand = 'isom', int $box_size = 24): string
	{
		$ftyp = pack('N', $box_size) . 'ftyp' . $brand . "\x00\x00\x02\x00" . 'isommp41';

		return $ftyp . str_repeat("\x00", $length - strlen($ftyp));
	}
}
