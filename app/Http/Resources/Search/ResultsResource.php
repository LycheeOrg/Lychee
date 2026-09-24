<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Search;

use App\Http\Resources\Models\PhotoResource;
use App\Http\Resources\Models\ThumbAlbumResource;
use App\Http\Resources\Traits\HasPrepPhotoCollection;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Result of a Search query.
 */
#[TypeScript()]
class ResultsResource extends Data
{
	use HasPrepPhotoCollection;

	/** @var Collection<int,ThumbAlbumResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.ThumbAlbumResource[]')]
	public Collection $albums;

	/** @var Collection<int,PhotoResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.PhotoResource[]')]
	public Collection $photos;

	public int $current_page;
	public int $from;
	public int $last_page;
	public int $per_page;
	public int $to;
	public int $total;

	/**
	 * @param Collection<int,ThumbAlbumResource>                                   $albums
	 * @param LengthAwarePaginator<int,PhotoResource>&Paginator<int,PhotoResource> $photos
	 *
	 * @return void
	 */
	public function __construct(
		Collection $albums,
		LengthAwarePaginator $photos,
	) {
		$this->albums = $albums;
		$this->photos = collect($photos->items());
		$this->current_page = $photos->currentPage();
		$this->from = $photos->firstItem() ?? 0;
		$this->last_page = $photos->lastPage();
		$this->per_page = $photos->perPage();
		$this->to = $photos->lastItem() ?? 0;
		$this->total = $photos->total();

		$this->prepPhotosCollection();
	}

	/**
	 * @param Collection<int,Album>           $albums
	 * @param LengthAwarePaginator<int,Photo> $photos
	 * @param string|null                     $album_id
	 *
	 * @return ResultsResource
	 */
	public static function fromData(Collection $albums, LengthAwarePaginator $photos, ?string $album_id): self
	{
		// Feature 070 (FR-070-03): one grouped query for the page, then a
		// per-photo answer. This previously took a single boolean derived from
		// the `grants_full_photo_access` config - a seed value for new shares,
		// not an authorization gate - and so over-granted full-resolution access.
		/** @var \App\Models\User|null $user */
		$user = \Illuminate\Support\Facades\Auth::user();
		$should_downgrade = resolve(\App\Actions\Photo\StructOfArrays\ResolvesPhotoGrants::class)
			->downgradeMap(collect($photos->items()), $user);

		/** @disregard Undefined method through() (stupid intelephense) */ return new self(
			albums: ThumbAlbumResource::collect($albums),
			/** @phpstan-ignore method.notFound (this methods exists, it's in the doc...) */
			photos: $photos->through(fn ($p) => new PhotoResource(
				photo: $p,
				album_id: $album_id,
				should_downgrade_size_variants: $should_downgrade[$p->id] ?? true,
			)),
		);
	}
}