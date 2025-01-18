<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Resources\Models;

use App\Enum\LicenseType;
use App\Enum\SizeVariantType;
use App\Facades\Helpers;
use App\Legacy\V1\Resources\Rights\PhotoRightsResource;
use App\Legacy\V1\Resources\Traits\WithStatus;
use App\Models\Configs;
use App\Models\Extensions\SizeVariants;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Policies\PhotoPolicy;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Facades\Gate;

/**
 * Photo resource returned when get() or adding a new photo.
 *
 * @property Photo $resource
 */
final class PhotoResource extends JsonResource
{
	use WithStatus;

	public function __construct(Photo $photo)
	{
		parent::__construct($photo);
	}

	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param Request                  $request
	 *
	 * @return array<string,mixed>|Arrayable<string,mixed>|\JsonSerializable
	 */
	public function toArray($request)
	{
		/** @var SizeVariants|MissingValue $size_variants */
		$size_variants = $this->whenLoaded('size_variants');
		if ($size_variants instanceof MissingValue) {
			$size_variants = null;
		}
		$downgrade = !Gate::check(PhotoPolicy::CAN_ACCESS_FULL_PHOTO, [Photo::class, $this->resource]) &&
			!$this->resource->isVideo() &&
			$size_variants?->hasMedium() === true;

		$medium = $size_variants?->getSizeVariant(SizeVariantType::MEDIUM);
		$medium2x = $size_variants?->getSizeVariant(SizeVariantType::MEDIUM2X);
		$original = $size_variants?->getSizeVariant(SizeVariantType::ORIGINAL);
		$small = $size_variants?->getSizeVariant(SizeVariantType::SMALL);
		$small2x = $size_variants?->getSizeVariant(SizeVariantType::SMALL2X);
		$thumb = $size_variants?->getSizeVariant(SizeVariantType::THUMB);
		$thumb2x = $size_variants?->getSizeVariant(SizeVariantType::THUMB2X);
		$placeholder = $size_variants?->getSizeVariant(SizeVariantType::PLACEHOLDER);

		return [
			'id' => $this->resource->id,
			'album_id' => $this->resource->album_id,
			'altitude' => $this->resource->altitude,
			'aperture' => $this->resource->aperture,
			'checksum' => $this->resource->checksum,
			'created_at' => $this->resource->created_at->toIso8601String(),
			'description' => $this->resource->description,
			'focal' => $this->resource->focal,
			'img_direction' => null,
			'is_starred' => $this->resource->is_starred,
			'iso' => $this->resource->iso,
			'latitude' => $this->resource->latitude,
			'lens' => $this->resource->lens,
			'license' => $this->resource->license,
			'live_photo_checksum' => $this->resource->live_photo_checksum,
			'live_photo_content_id' => $this->resource->live_photo_content_id,
			'live_photo_url' => $this->resource->live_photo_url,
			'location' => $this->resource->location,
			'longitude' => $this->resource->longitude,
			'make' => $this->resource->make,
			'model' => $this->resource->model,
			'original_checksum' => $this->resource->original_checksum,
			'shutter' => $this->resource->shutter,
			'size_variants' => [
				'medium' => $medium === null ? null : SizeVariantResource::make($medium)->toArray($request),
				'medium2x' => $medium2x === null ? null : SizeVariantResource::make($medium2x)->toArray($request),
				'original' => $original === null ? null : SizeVariantResource::make($original)->setNoUrl($downgrade)->toArray($request),
				'small' => $small === null ? null : SizeVariantResource::make($small)->toArray($request),
				'small2x' => $small2x === null ? null : SizeVariantResource::make($small2x)->toArray($request),
				'thumb' => $thumb === null ? null : SizeVariantResource::make($thumb)->toArray($request),
				'thumb2x' => $thumb2x === null ? null : SizeVariantResource::make($thumb2x)->toArray($request),
				'placeholder' => $placeholder === null ? null : SizeVariantResource::make($placeholder)->toArray($request),
			],
			'tags' => $this->resource->tags,
			'taken_at' => $this->resource->taken_at?->toIso8601String(),
			'taken_at_orig_tz' => $this->resource->taken_at_orig_tz,
			'title' => $this->resource->title,
			'type' => $this->resource->type,
			'updated_at' => $this->resource->updated_at->toIso8601String(),
			'rights' => PhotoRightsResource::make($this->resource)->toArray($request),
			'next_photo_id' => null,
			'previous_photo_id' => null,
			'preformatted' => $this->preformatted($original),
			'precomputed' => $this->precomputed(),
		];
	}

	/**
	 * @param SizeVariant|null $original
	 *
	 * @return array<string,mixed>
	 */
	private function preformatted(?SizeVariant $original): array
	{
		$overlay_date_format = Configs::getValueAsString('date_format_photo_overlay');
		$date_format_uploaded = Configs::getValueAsString('date_format_sidebar_uploaded');
		$date_format_taken_at = Configs::getValueAsString('date_format_sidebar_taken_at');

		return [
			'created_at' => $this->resource->created_at->format($date_format_uploaded),
			'taken_at' => $this->resource->taken_at?->format($date_format_taken_at),
			'date_overlay' => ($this->resource->taken_at ?? $this->resource->created_at)->format($overlay_date_format) ?? '',

			'shutter' => str_replace('s', 'sec', $this->resource->shutter ?? ''),
			'aperture' => str_replace('f/', '', $this->resource->aperture ?? ''),
			'iso' => sprintf(__('gallery.photo.details.iso'), $this->resource->iso),
			'lens' => ($this->resource->lens === '' || $this->resource->lens === null) ? '' : sprintf('(%s)', $this->resource->lens),

			'duration' => Helpers::secondsToHMS(intval($this->resource->aperture)),
			'fps' => $this->resource->focal === null ? $this->resource->focal . ' fps' : '',

			'filesize' => Helpers::getSymbolByQuantity($original?->filesize ?? 0),
			'resolution' => $original?->width . ' x ' . $original?->height,
			'latitude' => Helpers::decimalToDegreeMinutesSeconds($this->resource->latitude, true),
			'longitude' => Helpers::decimalToDegreeMinutesSeconds($this->resource->longitude, false),
			'altitude' => $this->resource->altitude !== null ? round($this->resource->altitude, 1) . 'm' : '',
			'license' => $this->resource->license !== LicenseType::NONE ? $this->resource->license->localization() : '',
			'description' => ($this->resource->description ?? '') === '' ? '' : Markdown::convert($this->resource->description)->getContent(),
		];
	}

	/**
	 * @return array<string,bool>
	 */
	private function precomputed(): array
	{
		return [
			'is_video' => $this->resource->isVideo(),
			'is_raw' => $this->resource->isRaw(),
			'is_livephoto' => $this->resource->live_photo_url !== null,
			'is_camera_date' => $this->resource->taken_at !== null,
			'has_exif' => $this->genExifHash() !== '',
			'has_location' => $this->has_location(),
		];
	}

	private function has_location(): bool
	{
		return $this->resource->longitude !== null &&
			$this->resource->latitude !== null &&
			$this->resource->altitude !== null;
	}

	private function genExifHash(): string
	{
		$exifHash = $this->resource->make;
		$exifHash .= $this->resource->model;
		$exifHash .= $this->resource->shutter;
		if (!$this->resource->isVideo()) {
			$exifHash .= $this->resource->aperture;
			$exifHash .= $this->resource->focal;
		}
		$exifHash .= $this->resource->iso;

		return $exifHash;
	}
}
