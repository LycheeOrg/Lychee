<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Tags;

use App\Http\Resources\Models\PhotoResource;
use App\Http\Resources\Models\ThumbAlbumResource;
use App\Http\Resources\Traits\HasPrepPhotoCollection;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class TagWithPhotosAndAlbumsResource extends Data
{
	use HasPrepPhotoCollection;

	public int $id;
	public string $name;
	#[LiteralTypeScriptType('App.Http.Resources.Models.PhotoResource[]')]
	public Collection $photos;
	#[LiteralTypeScriptType('App.Http.Resources.Models.ThumbAlbumResource[]')]
	public Collection $albums;

	/**
	 * @param Collection<int,PhotoResource>      $photos
	 * @param Collection<int,ThumbAlbumResource> $albums
	 */
	public function __construct(
		int $id,
		string $name,
		Collection $photos,
		Collection $albums,
	) {
		$this->id = $id;
		$this->name = $name;
		$this->photos = $photos;
		$this->albums = $albums;
		$this->prepPhotosCollection();
	}
}
