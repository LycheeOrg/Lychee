<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Flow;

use App\Contracts\Models\AbstractAlbum;
use App\Enum\DateOrderingType;
use App\Http\Resources\Models\AlbumStatisticsResource;
use App\Http\Resources\Models\PhotoResource;
use App\Http\Resources\Models\SizeVariantsResouce;
use App\Http\Resources\Traits\HasPrepPhotoCollection;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Support\Carbon;
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
	public string $diff_published_created_at;
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

		$this->setPhotos($album);
		$should_downgrade = $album->cover !== null && !Gate::check(AlbumPolicy::CAN_ACCESS_FULL_PHOTO, [AbstractAlbum::class, $album]);
		$this->cover = $album->cover !== null ? new SizeVariantsResouce($album->cover, $should_downgrade) : null;

		// We use the short circuiting operator here to avoid checking is_recursive_nsfw if we hide them already.
		$this->is_nsfw = request()->configs()->getValueAsBool('hide_nsfw_in_flow') === false && $album->is_recursive_nsfw;

		if ($this->photos !== null) {
			// Prep collection with first and last link + which id is next.
			$this->prepPhotosCollection();
		}

		$format_date = request()->configs()->getValueAsString('date_format_flow_published');
		$published_created_at = $album->published_at ?? $album->created_at;
		if (is_string($published_created_at)) {
			$published_created_at = new Carbon($published_created_at);
		}
		$this->diff_published_created_at = $published_created_at->diffForHumans();
		$this->published_created_at = $published_created_at->format($format_date);

		$this->num_photos = $album->num_photos;
		$this->num_children = $album->num_children;

		$this->setMinMax($album);

		if (request()->configs()->getValueAsBool('metrics_enabled') &&
			request()->configs()->getValueAsBool('flow_display_statistics') &&
			Gate::check(AlbumPolicy::CAN_READ_METRICS, [Album::class, $album])
		) {
			$this->statistics = AlbumStatisticsResource::fromModel($album->statistics);
		}
	}

	/**
	 * Set the photo resources for the album.
	 * This also validates a possible case where an album is present without photos to be displayed in the flow.
	 *
	 * @param Album $album
	 *
	 * @return void
	 */
	private function setPhotos(Album $album): void
	{
		if (request()->configs()->getValueAsBool('flow_include_photos_from_children') && ($album->photos === null || $album->photos->isEmpty())) {
			// Really NOT recommended!
			// @codeCoverageIgnoreStart
			$album->load(['all_photos', 'all_photos.size_variants', 'all_photos.palette', 'all_photos.statistics']);
			$this->photos = $this->toPhotoResources(
				photos: $album->all_photos,
				album_id: $album->id,
				should_downgrade: !Gate::check(AlbumPolicy::CAN_ACCESS_FULL_PHOTO, [AbstractAlbum::class, $album]));

			return;
			// @codeCoverageIgnoreEnd
		}

		if ($album->photos !== null && !$album->photos->isEmpty()) {
			$this->photos = $this->toPhotoResources(
				photos: $album->photos,
				album_id: $album->id,
				should_downgrade: !Gate::check(AlbumPolicy::CAN_ACCESS_FULL_PHOTO, [AbstractAlbum::class, $album]),
			);

			return;
		}

		// @codeCoverageIgnoreStart
		if (config('app.debug') === true) {
			throw new \LogicException(sprintf('Album %s has no photos, but flow_include_photos_from_children is false.', $album->id));
		}

		$this->photos = resolve(Collection::class);
		// @codeCoverageIgnoreEnd
	}

	private function setMinMax(Album $album): void
	{
		if (request()->configs()->getValueAsBool('flow_min_max_enabled') === false) {
			return;
		}

		$min_max_date_format = request()->configs()->getValueAsString('date_format_flow_min_max');
		$min_taken_at = $album->min_taken_at?->format($min_max_date_format);
		$max_taken_at = $album->max_taken_at?->format($min_max_date_format);

		$this->min_max_text = match (true) {
			$min_taken_at === null || $max_taken_at === null => null,
			$min_taken_at === $max_taken_at => $min_taken_at,
			request()->configs()->getValueAsEnum('flow_min_max_order', DateOrderingType::class) === DateOrderingType::YOUNGER_OLDER => $max_taken_at . ' - ' . $min_taken_at,
			default => $min_taken_at . ' - ' . $max_taken_at,
		};
	}
}