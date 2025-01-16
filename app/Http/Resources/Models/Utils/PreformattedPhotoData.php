<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models\Utils;

use App\Enum\LicenseType;
use App\Facades\Helpers;
use App\Http\Resources\Models\SizeVariantResource;
use App\Models\Configs;
use App\Models\Photo;
use GrahamCampbell\Markdown\Facades\Markdown;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PreformattedPhotoData extends Data
{
	public string $created_at;
	public ?string $taken_at;
	public string $date_overlay;
	public string $shutter;
	public string $aperture;
	public string $iso;
	public string $lens;
	public string $duration;
	public string $fps;
	public string $filesize;
	public string $resolution;
	public ?string $latitude;
	public ?string $longitude;
	public ?string $altitude;
	public string $license;
	public string $description;

	public function __construct(Photo $photo, ?SizeVariantResource $original = null)
	{
		$overlay_date_format = Configs::getValueAsString('date_format_photo_overlay');
		$date_format_uploaded = Configs::getValueAsString('date_format_sidebar_uploaded');
		$date_format_taken_at = Configs::getValueAsString('date_format_sidebar_taken_at');

		$this->created_at = $photo->created_at->format($date_format_uploaded);
		$this->taken_at = $photo->taken_at?->format($date_format_taken_at);
		$this->date_overlay = ($photo->taken_at ?? $photo->created_at)->format($overlay_date_format) ?? '';

		$this->shutter = str_replace('s', 'sec', $photo->shutter ?? '');
		$this->aperture = str_replace('f/', '', $photo->aperture ?? '');
		$this->iso = sprintf(__('gallery.photo.details.iso'), $photo->iso);
		$this->lens = ($photo->lens === '' || $photo->lens === null) ? '' : sprintf('(%s)', $photo->lens);

		$this->duration = Helpers::secondsToHMS(intval($photo->aperture));
		$this->fps = $photo->focal === null ? $photo->focal . ' fps' : '';

		$this->filesize = $original?->filesize ?? '0';
		$this->resolution = $original?->width . ' x ' . $original?->height;
		$this->latitude = Helpers::decimalToDegreeMinutesSeconds($photo->latitude, true);
		$this->longitude = Helpers::decimalToDegreeMinutesSeconds($photo->longitude, false);
		$this->altitude = $photo->altitude !== null ? round($photo->altitude, 1) . 'm' : null;
		$this->license = $photo->license !== LicenseType::NONE ? $photo->license->localization() : '';
		$this->description = ($photo->description ?? '') === '' ? '' : Markdown::convert($photo->description)->getContent();
	}
}
