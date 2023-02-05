<?php

namespace App\Http\Resources\Models;

use App\Enum\SizeVariantType;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\InvalidSizeVariantException;
use App\Http\Resources\Rights\PhotoRightsResource;
use App\Http\Resources\Traits\WithStatus;
use App\Models\Extensions\SizeVariants;
use App\Models\Photo;
use App\Policies\PhotoPolicy;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Facades\Gate;
use JsonSerializable;

/**
 * Photo resource returned when get() or adding a new photo.
 * @property Photo $resource
 */
class PhotoResource extends JsonResource
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
	 *
	 * @param Request $request
	 * @return array|Arrayable|JsonSerializable
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
			'is_public' => $this->resource->is_public,
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
				'original' => $original === null ? null : SizeVariantResource::make($original)->noUrl($downgrade)->toArray($request),
				'small' => $small === null ? null : SizeVariantResource::make($small)->toArray($request),
				'small2x' => $small2x === null ? null : SizeVariantResource::make($small2x)->toArray($request),
				'thumb' => $thumb === null ? null : SizeVariantResource::make($thumb)->toArray($request),
				'thumb2x' => $thumb2x === null ? null : SizeVariantResource::make($thumb2x)->toArray($request),
			],
			'tags' => $this->resource->tags,
			'taken_at' => $this->resource->taken_at?->toIso8601String(),
			'taken_at_orig_tz' => $this->resource->taken_at_orig_tz,
			'title' => $this->resource->title,
			'type' => $this->resource->type,
			'updated_at' => $this->resource->updated_at->toIso8601String(),
			'rights' => PhotoRightsResource::make($this->resource)->toArray($request),
			'next_photo_id' => null,
			'previous_photo_id' => null
		];
	}
}
