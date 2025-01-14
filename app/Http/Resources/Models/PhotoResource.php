<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Enum\LicenseType;
use App\Http\Resources\Models\Utils\PreComputedPhotoData;
use App\Http\Resources\Models\Utils\PreformattedPhotoData;
use App\Http\Resources\Models\Utils\TimelineData;
use App\Http\Resources\Rights\PhotoRightsResource;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PhotoResource extends Data
{
	public string $id;
	public ?string $album_id;
	public ?float $altitude;
	public ?string $aperture;
	public string $checksum;
	public string $created_at;
	public string $description;
	public ?string $focal;
	public bool $is_starred;
	public ?string $iso;
	public ?float $latitude;
	public ?string $lens;
	public LicenseType $license;
	public ?string $live_photo_checksum;
	public ?string $live_photo_content_id;
	public ?string $live_photo_url;
	public ?string $location;
	public ?float $longitude;
	public ?string $make;
	public ?string $model;
	public string $original_checksum;
	public ?string $shutter;
	public SizeVariantsResouce $size_variants;
	/** @var string[] */
	public array $tags;
	public ?string $taken_at;
	public ?string $taken_at_orig_tz;
	public string $title;
	public string $type;
	public string $updated_at;
	public PhotoRightsResource $rights;
	public ?string $next_photo_id;
	public ?string $previous_photo_id;
	public PreformattedPhotoData $preformatted;
	public PreComputedPhotoData $precomputed;
	public ?TimelineData $timeline = null;

	private Carbon $timeline_data_carbon;

	public function __construct(Photo $photo)
	{
		$this->id = $photo->id;
		$this->album_id = $photo->album_id;
		$this->altitude = $photo->altitude;
		$this->aperture = $photo->aperture;
		$this->checksum = $photo->checksum;
		$this->created_at = $photo->created_at->toIso8601String();
		$this->description = $photo->description ?? '';
		$this->focal = $photo->focal;
		$this->is_starred = $photo->is_starred;
		$this->iso = $photo->iso;
		$this->latitude = $photo->latitude;
		$this->lens = $photo->lens;
		$this->license = $photo->license;
		$this->live_photo_checksum = $photo->live_photo_checksum;
		$this->live_photo_content_id = $photo->live_photo_content_id;
		$this->live_photo_url = $photo->live_photo_url;
		$this->setLocation($photo);
		$this->longitude = $photo->longitude;
		$this->make = $photo->make;
		$this->model = $photo->model;
		$this->original_checksum = $photo->original_checksum;
		$this->shutter = $photo->shutter;
		$this->size_variants = new SizeVariantsResouce($photo);
		$this->tags = $photo->tags;
		$this->taken_at = $photo->taken_at?->toIso8601String();
		$this->taken_at_orig_tz = $photo->taken_at_orig_tz;
		$this->title = $photo->title;
		$this->type = $photo->type;
		$this->updated_at = $photo->updated_at->toIso8601String();
		$this->rights = new PhotoRightsResource($photo);
		$this->next_photo_id = null;
		$this->previous_photo_id = null;
		$this->preformatted = new PreformattedPhotoData($photo, $this->size_variants->original);
		$this->precomputed = new PreComputedPhotoData($photo);

		$this->timeline_data_carbon = $photo->taken_at ?? $photo->created_at;
	}

	public static function fromModel(Photo $photo): PhotoResource
	{
		return new self($photo);
	}

	private function setLocation(Photo $photo): void
	{
		$showLocation = Configs::getValueAsBool('location_show') && (Auth::check() || Configs::getValueAsBool('location_show_public'));
		$this->location = $showLocation ? $photo->location : null;
	}

	/**
	 * Accessors to the Carbon instances.
	 *
	 * @return Carbon
	 */
	public function timeline_date_carbon(): Carbon
	{
		return $this->timeline_data_carbon;
	}
}
