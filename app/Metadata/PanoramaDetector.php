<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Metadata;

use PHPExif\Exif;

/**
 * Decides from the XMP-GPano tags whether a photo is an equirectangular
 * 360° photo, and where a partial photo sphere sits in its full panorama.
 *
 * GPano crop values are expressed in pixels of the image the tags were
 * written for; they are scaled to the width of the file actually read.
 */
final class PanoramaDetector
{
	private const EQUIRECTANGULAR = 'equirectangular';

	/**
	 * @param Exif $exif  metadata of the file
	 * @param int  $width pixel width of the file
	 */
	public static function detect(Exif $exif, int $width): PanoramaInfo
	{
		if (!self::isEquirectangular($exif)) {
			return PanoramaInfo::flat();
		}

		$crop = self::readCrop($exif);
		if (self::isCropAbsent($crop)) {
			return PanoramaInfo::fullSphere();
		}

		if (!self::isCropValid($crop) || $width <= 0) {
			return PanoramaInfo::flat();
		}

		if (self::coversWholePanorama($crop)) {
			return PanoramaInfo::fullSphere();
		}

		return self::scale($crop, $width / $crop['width']);
	}

	private static function isEquirectangular(Exif $exif): bool
	{
		return $exif->getProjectionType() === self::EQUIRECTANGULAR && $exif->getUsePanoramaViewer() !== false;
	}

	/**
	 * @return array{full_width:int|false,full_height:int|false,left:int|false,top:int|false,width:int|false,height:int|false}
	 */
	private static function readCrop(Exif $exif): array
	{
		return [
			'full_width' => $exif->getFullPanoWidthPixels(),
			'full_height' => $exif->getFullPanoHeightPixels(),
			'left' => $exif->getCroppedAreaLeftPixels(),
			'top' => $exif->getCroppedAreaTopPixels(),
			'width' => $exif->getCroppedAreaImageWidthPixels(),
			'height' => $exif->getCroppedAreaImageHeightPixels(),
		];
	}

	/**
	 * @param array<string,int|false> $crop
	 */
	private static function isCropAbsent(array $crop): bool
	{
		return array_filter($crop, fn (int|false $value) => $value !== false) === [];
	}

	/**
	 * @param array<string,int|false> $crop
	 *
	 * @phpstan-assert-if-true array{full_width:int,full_height:int,left:int,top:int,width:int,height:int} $crop
	 */
	private static function isCropValid(array $crop): bool
	{
		if (in_array(false, $crop, true)) {
			return false;
		}

		return $crop['full_width'] > 0 && $crop['full_height'] > 0 &&
			$crop['width'] > 0 && $crop['height'] > 0 &&
			$crop['left'] >= 0 && $crop['top'] >= 0 &&
			$crop['left'] + $crop['width'] <= $crop['full_width'] &&
			$crop['top'] + $crop['height'] <= $crop['full_height'];
	}

	/**
	 * @param array{full_width:int,full_height:int,left:int,top:int,width:int,height:int} $crop
	 */
	private static function coversWholePanorama(array $crop): bool
	{
		return $crop['left'] === 0 && $crop['top'] === 0 &&
			$crop['width'] === $crop['full_width'] && $crop['height'] === $crop['full_height'];
	}

	/**
	 * @param array{full_width:int,full_height:int,left:int,top:int,width:int,height:int} $crop
	 */
	private static function scale(array $crop, float $factor): PanoramaInfo
	{
		return new PanoramaInfo(
			is_360: true,
			full_width: (int) round($crop['full_width'] * $factor),
			full_height: (int) round($crop['full_height'] * $factor),
			crop_left: (int) round($crop['left'] * $factor),
			crop_top: (int) round($crop['top'] * $factor),
		);
	}
}
