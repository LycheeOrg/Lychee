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

namespace Tests\Unit\Metadata;

use App\Metadata\PanoramaDetector;
use App\Metadata\PanoramaInfo;
use PHPExif\Exif;
use Tests\AbstractTestCase;

/**
 * Covers Feature 082's detection rule (FR-082-02): projection, viewer flag,
 * and the absent / valid / malformed crop branches.
 */
class PanoramaDetectorTest extends AbstractTestCase
{
	public function testFullSphere(): void
	{
		$exif = $this->equirectangular();

		$this->assertFullSphere(PanoramaDetector::detect($exif, 1024));
	}

	public function testFullSphereWithoutViewerFlag(): void
	{
		$exif = (new Exif())->setProjectionType('equirectangular');

		$this->assertFullSphere(PanoramaDetector::detect($exif, 1024));
	}

	public function testPartialSphereIsScaledToTheFileWidth(): void
	{
		$exif = $this->withCrop($this->equirectangular(), 8000, 4000, 1000, 1000, 6000, 2000);

		$info = PanoramaDetector::detect($exif, 600);

		self::assertTrue($info->is_360);
		self::assertSame(800, $info->full_width);
		self::assertSame(400, $info->full_height);
		self::assertSame(100, $info->crop_left);
		self::assertSame(100, $info->crop_top);
	}

	public function testPartialSphereScalingIsRounded(): void
	{
		$exif = $this->withCrop($this->equirectangular(), 8000, 4000, 1000, 1000, 3000, 1500);

		$info = PanoramaDetector::detect($exif, 1000);

		self::assertTrue($info->is_360);
		self::assertSame(2667, $info->full_width);
		self::assertSame(1333, $info->full_height);
		self::assertSame(333, $info->crop_left);
		self::assertSame(333, $info->crop_top);
	}

	public function testCropCoveringTheWholePanoramaIsAFullSphere(): void
	{
		$exif = $this->withCrop($this->equirectangular(), 4000, 2000, 0, 0, 4000, 2000);

		$this->assertFullSphere(PanoramaDetector::detect($exif, 1024));
	}

	public function testViewerDisabledIsFlat(): void
	{
		$exif = $this->equirectangular()->setUsePanoramaViewer(false);

		$this->assertFlat(PanoramaDetector::detect($exif, 1024));
	}

	public function testOtherProjectionIsFlat(): void
	{
		$exif = (new Exif())->setProjectionType('cylindrical')->setUsePanoramaViewer(true);

		$this->assertFlat(PanoramaDetector::detect($exif, 1024));
	}

	public function testNoProjectionIsFlat(): void
	{
		$this->assertFlat(PanoramaDetector::detect(new Exif(), 1024));
	}

	public function testIncompleteCropIsFlat(): void
	{
		$exif = $this->equirectangular()
			->setFullPanoWidthPixels(8000)
			->setFullPanoHeightPixels(4000)
			->setCroppedAreaLeftPixels(1000);

		$this->assertFlat(PanoramaDetector::detect($exif, 1024));
	}

	public function testCropOutsideThePanoramaIsFlat(): void
	{
		$exif = $this->withCrop($this->equirectangular(), 8000, 4000, 3000, 1000, 6000, 2000);

		$this->assertFlat(PanoramaDetector::detect($exif, 600));
	}

	public function testZeroCropSizeIsFlat(): void
	{
		$exif = $this->withCrop($this->equirectangular(), 8000, 4000, 0, 0, 0, 2000);

		$this->assertFlat(PanoramaDetector::detect($exif, 600));
	}

	public function testNegativeCropOffsetIsFlat(): void
	{
		$exif = $this->withCrop($this->equirectangular(), 8000, 4000, -10, 0, 6000, 2000);

		$this->assertFlat(PanoramaDetector::detect($exif, 600));
	}

	public function testUnknownFileWidthWithCropIsFlat(): void
	{
		$exif = $this->withCrop($this->equirectangular(), 8000, 4000, 1000, 1000, 6000, 2000);

		$this->assertFlat(PanoramaDetector::detect($exif, 0));
	}

	private function equirectangular(): Exif
	{
		return (new Exif())->setProjectionType('equirectangular')->setUsePanoramaViewer(true);
	}

	private function withCrop(Exif $exif, int $full_width, int $full_height, int $left, int $top, int $width, int $height): Exif
	{
		return $exif
			->setFullPanoWidthPixels($full_width)
			->setFullPanoHeightPixels($full_height)
			->setCroppedAreaLeftPixels($left)
			->setCroppedAreaTopPixels($top)
			->setCroppedAreaImageWidthPixels($width)
			->setCroppedAreaImageHeightPixels($height);
	}

	private function assertFullSphere(PanoramaInfo $info): void
	{
		self::assertTrue($info->is_360);
		self::assertNull($info->full_width);
		self::assertNull($info->full_height);
		self::assertNull($info->crop_left);
		self::assertNull($info->crop_top);
	}

	private function assertFlat(PanoramaInfo $info): void
	{
		self::assertFalse($info->is_360);
		self::assertNull($info->full_width);
		self::assertNull($info->full_height);
		self::assertNull($info->crop_left);
		self::assertNull($info->crop_top);
	}
}
