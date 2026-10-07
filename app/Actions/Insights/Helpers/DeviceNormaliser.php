<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights\Helpers;

use App\Enum\DeviceCategory;
use function Safe\preg_match;
use function Safe\preg_replace;

/**
 * Groups EXIF `make`/`model` pairs into devices (Feature 085, FR-085-12).
 *
 * Maker spellings are reduced to a key (lower case, punctuation and company
 * suffixes removed) and mapped to one manufacturer name. Unknown makers keep
 * their trimmed EXIF spelling. Classification: a phone model keyword wins,
 * then the maker's usual product line (phones or cameras), else "other".
 * Results are memoised: a library repeats the same few pairs.
 */
final class DeviceNormaliser
{
	private const COMPANY_SUFFIXES = [
		'ag', 'camera', 'co', 'communications', 'company', 'computer', 'corp', 'corporation', 'digital',
		'electronics', 'gmbh', 'imaging', 'inc', 'limited', 'ltd', 'mobile', 'optical', 'solutions',
		'technologies', 'technology',
	];

	private const MANUFACTURERS = [
		'apple' => 'Apple',
		'arashi vision' => 'Insta360',
		'asahi' => 'Pentax',
		'asus' => 'Asus',
		'blackberry' => 'BlackBerry',
		'canon' => 'Canon',
		'casio' => 'Casio',
		'dji' => 'DJI',
		'eastman kodak' => 'Kodak',
		'fairphone' => 'Fairphone',
		'fuji photo film' => 'Fujifilm',
		'fujifilm' => 'Fujifilm',
		'google' => 'Google',
		'gopro' => 'GoPro',
		'hasselblad' => 'Hasselblad',
		'hmd global' => 'Nokia',
		'honor' => 'Honor',
		'htc' => 'HTC',
		'huawei' => 'Huawei',
		'insta360' => 'Insta360',
		'kodak' => 'Kodak',
		'konica minolta' => 'Konica Minolta',
		'leica' => 'Leica',
		'lg' => 'LG',
		'lge' => 'LG',
		'minolta' => 'Konica Minolta',
		'motorola' => 'Motorola',
		'nikon' => 'Nikon',
		'nokia' => 'Nokia',
		'nothing' => 'Nothing',
		'olympus' => 'Olympus',
		'om' => 'OM System',
		'om system' => 'OM System',
		'oneplus' => 'OnePlus',
		'oppo' => 'OPPO',
		'panasonic' => 'Panasonic',
		'pentax' => 'Pentax',
		'phase one' => 'Phase One',
		'realme' => 'realme',
		'ricoh' => 'Ricoh',
		'samsung' => 'Samsung',
		'samsung techwin' => 'Samsung',
		'sigma' => 'Sigma',
		'sony' => 'Sony',
		'sony ericsson' => 'Sony',
		'vivo' => 'vivo',
		'xiaomi' => 'Xiaomi',
		'zte' => 'ZTE',
	];

	private const PHONE_MAKERS = [
		'Apple', 'Asus', 'BlackBerry', 'Fairphone', 'Google', 'HTC', 'Honor', 'Huawei', 'LG', 'Motorola',
		'Nokia', 'Nothing', 'OPPO', 'OnePlus', 'Xiaomi', 'ZTE', 'realme', 'vivo',
	];

	private const CAMERA_MAKERS = [
		'Canon', 'Casio', 'DJI', 'Fujifilm', 'GoPro', 'Hasselblad', 'Insta360', 'Kodak', 'Konica Minolta',
		'Leica', 'Nikon', 'OM System', 'Olympus', 'Panasonic', 'Pentax', 'Phase One', 'Ricoh', 'Samsung',
		'Sigma', 'Sony',
	];

	private const PHONE_MODEL_PATTERN = '/\b(iphone|ipad|pixel|galaxy|xperia|nexus|moto|lumia|redmi|poco)\b|^(sm|gt|xq)-/i';

	/** @var array<string,Device> */
	private array $memo = [];

	public function normalise(?string $make, ?string $model): Device
	{
		return $this->memo[$make . "\0" . $model] ??= self::resolve(self::clean($make), self::clean($model));
	}

	private static function resolve(?string $make, ?string $model): Device
	{
		$manufacturer = $make === null ? null : (self::MANUFACTURERS[self::key($make)] ?? $make);

		return new Device(
			manufacturer: $manufacturer,
			name: self::name($manufacturer, $model),
			category: self::category($manufacturer, $model),
		);
	}

	private static function clean(?string $value): ?string
	{
		$trimmed = trim($value ?? '');

		return $trimmed === '' ? null : $trimmed;
	}

	/**
	 * "NIKON CORPORATION" → "nikon", "OM Digital Solutions" → "om".
	 */
	private static function key(string $make): string
	{
		$words = explode(' ', trim(preg_replace('/[\s.,]+/', ' ', strtolower($make))));
		while (count($words) > 1 && in_array($words[count($words) - 1], self::COMPANY_SUFFIXES, true)) {
			array_pop($words);
		}

		return implode(' ', $words);
	}

	/**
	 * Model prefixed with the manufacturer, without repeating it.
	 */
	private static function name(?string $manufacturer, ?string $model): ?string
	{
		if ($model === null || $manufacturer === null) {
			return $model ?? $manufacturer;
		}

		if (str_starts_with(strtolower($model), strtolower($manufacturer))) {
			return $manufacturer . substr($model, strlen($manufacturer));
		}

		return $manufacturer . ' ' . $model;
	}

	private static function category(?string $manufacturer, ?string $model): DeviceCategory
	{
		if ($model !== null && preg_match(self::PHONE_MODEL_PATTERN, $model) === 1) {
			return DeviceCategory::MOBILE;
		}

		return match (true) {
			in_array($manufacturer, self::PHONE_MAKERS, true) => DeviceCategory::MOBILE,
			in_array($manufacturer, self::CAMERA_MAKERS, true) => DeviceCategory::CAMERA,
			default => DeviceCategory::OTHER,
		};
	}
}
