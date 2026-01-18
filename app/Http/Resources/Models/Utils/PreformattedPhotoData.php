<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models\Utils;

use App\Enum\LicenseType;
use App\Facades\Helpers;
use App\Http\Resources\Models\SizeVariantResource;
use App\Models\Photo;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PreformattedPhotoData extends Data
{
	public string $created_at;
	public ?string $taken_at;
	public string $date_overlay;
	public ?string $make = null;
	public ?string $model = null;
	public string $shutter = '';
	public string $aperture = '';
	public string $iso = '';
	public string $lens = '';
	public ?string $focal = null;
	public string $duration = '';
	public string $fps = '';
	public string $filesize = '';
	public string $resolution = '';
	public ?string $latitude = null;
	public ?string $longitude = null;
	public ?string $altitude = null;
	public ?string $location = null;
	public string $license = '';
	public string $description;

	public function __construct(Photo $photo, bool $include_exif_data, ?SizeVariantResource $original = null)
	{
		$overlay_date_format = request()->configs()->getValueAsString('date_format_photo_overlay');
		$date_format_uploaded = request()->configs()->getValueAsString('date_format_sidebar_uploaded');
		$date_format_taken_at = request()->configs()->getValueAsString('date_format_sidebar_taken_at');

		$this->created_at = $photo->created_at->format($date_format_uploaded);
		$this->taken_at = $photo->taken_at?->format($date_format_taken_at);
		$this->date_overlay = ($photo->taken_at ?? $photo->created_at)->format($overlay_date_format) ?? '';
		$this->description = ($photo->description ?? '') === '' ? '' : Markdown::convert($photo->description)->getContent();
		$this->license = $photo->license !== LicenseType::NONE ? $photo->license->localization() : '';

		if (!$include_exif_data) {
			return;
		}

		$this->make = $photo->make;
		$this->model = $photo->model;
		$this->focal = $photo->focal;
		$this->shutter = str_replace('s', 'sec', $photo->shutter ?? '');
		$this->aperture = str_replace('f/', '', $photo->aperture ?? '');
		$this->iso = sprintf(__('gallery.photo.details.iso'), $photo->iso);
		$this->lens = $photo->lens ?? '';

		$this->duration = Helpers::secondsToHMS(intval($photo->duration));
		$this->fps = $photo->fps !== null && $photo->fps !== '' ? $photo->fps . ' fps' : '';

		$this->filesize = $original?->filesize ?? '0';
		$this->resolution = $original?->width . ' x ' . $original?->height;
		$this->latitude = Helpers::decimalToDegreeMinutesSeconds($photo->latitude, true);
		$this->longitude = Helpers::decimalToDegreeMinutesSeconds($photo->longitude, false);
		$this->altitude = $photo->altitude !== null ? round($photo->altitude, 1) . 'm' : null;

		$show_location = request()->configs()->getValueAsBool('location_show') && (Auth::check() || request()->configs()->getValueAsBool('location_show_public'));
		$this->location = $show_location ? $photo->location : null;
	}
}
