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

namespace Tests\Unit\Services\Image;

use App\Services\Image\FileExtensionService;
use function Safe\file_put_contents;
use function Safe\tempnam;
use function Safe\unlink;
use Tests\AbstractTestCase;
use Tests\Constants\TestConstants;

/**
 * JPEG XL extension, MIME type and magic-byte detection (Feature 101, FR-101-01, FR-101-02).
 */
class FileExtensionServiceJxlTest extends AbstractTestCase
{
	/** Bare JXL codestream: signature `FF 0A` followed by a size header. */
	private const CODESTREAM = "\xFF\x0A\xFA\x7F\x01\x90\x08\x06\x01\x00\x48\x01\x00\x00";

	/** ISO-BMFF JXL container: `JXL ` signature box followed by an `ftyp` box. */
	private const CONTAINER = "\x00\x00\x00\x0CJXL \x0D\x0A\x87\x0A\x00\x00\x00\x14ftypjxl ";

	/** @var string[] */
	private array $temp_files = [];

	protected function tearDown(): void
	{
		foreach ($this->temp_files as $file) {
			unlink($file);
		}
		parent::tearDown();
	}

	public function testExtensionAndMimeType(): void
	{
		$service = new FileExtensionService();

		$this->assertTrue($service->isSupportedImageFileExtension('.jxl'));
		$this->assertTrue($service->isSupportedImageFileExtension('.JXL'));
		$this->assertTrue($service->isSupportedImageMimeType('image/jxl'));
		$this->assertSame('.jxl', $service->getDefaultFileExtensionForMimeType('image/jxl'));
	}

	public function testCodestreamIsSupported(): void
	{
		// S-101-06
		$path = $this->writeTempFile(self::CODESTREAM);

		$this->assertTrue((new FileExtensionService())->isSupportedImage($path, 'image/jxl', '.jxl'));
	}

	public function testContainerIsSupported(): void
	{
		// S-101-06
		$path = $this->writeTempFile(self::CONTAINER);

		$this->assertTrue((new FileExtensionService())->isSupportedImage($path, 'image/jxl', '.jxl'));
	}

	public function testJxlExtensionWithJpegContentIsRejected(): void
	{
		// S-101-05
		$path = base_path(TestConstants::SAMPLE_FILE_NIGHT_IMAGE);

		$this->assertFalse((new FileExtensionService())->isSupportedImage($path, 'image/jxl', '.jxl'));
	}

	public function testJxlContentWithJpegExtensionIsRejected(): void
	{
		$path = $this->writeTempFile(self::CODESTREAM);

		$this->assertFalse((new FileExtensionService())->isSupportedImage($path, 'image/jpeg', '.jpg'));
	}

	private function writeTempFile(string $content): string
	{
		$path = tempnam(sys_get_temp_dir(), 'lychee-jxl-');
		file_put_contents($path, $content);
		$this->temp_files[] = $path;

		return $path;
	}
}
