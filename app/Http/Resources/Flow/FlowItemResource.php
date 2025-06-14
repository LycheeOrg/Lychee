<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Flow;

use App\Enum\DateOrderingType;
use App\Http\Resources\Models\AlbumStatisticsResource;
use App\Http\Resources\Models\PhotoResource;
use App\Http\Resources\Models\SizeVariantsResouce;
use App\Http\Resources\Traits\HasPrepPhotoCollection;
use App\Models\Album;
use App\Models\Configs;
use App\Policies\AlbumPolicy;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Result of a Search query.
 */
#[TypeScript()]
class FlowItemResource extends Data
{
	use HasPrepPhotoCollection;

	public string $id;
	public string $title;
	public ?string $description = null;
	public ?string $min_max_text = null;
	public string $published_created_at;
	public ?string $owner_name;
	public bool $is_nsfw;
	public int $num_photos = 0;
	public int $num_children = 0;
	public ?SizeVariantsResouce $cover;

	/** @var ?Collection<int,PhotoResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.PhotoResource[]')]
	public ?Collection $photos;
	public ?AlbumStatisticsResource $statistics = null;

	/**
	 * @return void
	 */
	public function __construct(
		Album $album,
	) {
		$this->id = $album->id;
		$this->title = $album->title;
		$this->owner_name = Auth::check() ? $album->owner->name : null;
		$this->description = Markdown::convert(trim($album->description ?? ''))->getContent();

		$this->photos = $album->relationLoaded('photos') ? $this->toPhotoResources($album->photos, $album) : null;
		$this->cover = $album->cover !== null ? new SizeVariantsResouce($album->cover, $album) : null;

		// TODO: Recompute this to take account that the album might be already in another sensitive one...
		$this->is_nsfw = $album->is_nsfw;

		if ($this->photos !== null) {
			// Prep collection with first and last link + which id is next.
			$this->prepPhotosCollection();
		}

		$format_date = Configs::getValueAsString('date_format_flow_published');
		$this->published_created_at = $album->published_at?->format($format_date) ?? $album->created_at->format($format_date);
		$this->num_photos = $album->num_photos;
		$this->num_children = $album->num_children;

		$this->setMinMax($album);

		if (Configs::getValueAsBool('metrics_enabled') &&
			Configs::getValueAsBool('flow_display_statistics') &&
			Gate::check(AlbumPolicy::CAN_READ_METRICS, [Album::class, $album])
		) {
			$this->statistics = AlbumStatisticsResource::fromModel($album->statistics);
		}
	}

	public static function fromModel(Album $album): FlowItemResource
	{
		return new self($album);
	}

	private function setMinMax(Album $album): void
	{
		if (Configs::getValueAsBool('flow_min_max_enabled') === false) {
			return;
		}

		$min_max_date_format = Configs::getValueAsString('date_format_flow_min_max');
		$min_taken_at = $album->min_taken_at?->format($min_max_date_format);
		$max_taken_at = $album->max_taken_at?->format($min_max_date_format);

		$this->min_max_text = match (true) {
			$min_taken_at === null || $max_taken_at === null => null,
			$min_taken_at === $max_taken_at => $min_taken_at,
			Configs::getValueAsEnum('flow_min_max_order', DateOrderingType::class) === DateOrderingType::YOUNGER_OLDER => $max_taken_at . ' - ' . $min_taken_at,
			default => $min_taken_at . ' - ' . $max_taken_at,
		};
	}
}