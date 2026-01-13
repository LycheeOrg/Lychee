<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Embed;

use App\Contracts\Models\AbstractAlbum;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Resource for embedding album data on external websites.
 *
 * Provides minimal album information and photo collection optimized for
 * external embedding. Includes only publicly visible data.
 */
#[TypeScript()]
class EmbedAlbumResource extends Data
{
	public EmbedAlbumInfo $album;
	/** @var Collection<int, EmbedPhotoResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Embed.EmbedPhotoResource[]')]
	public Collection $photos;

	public function __construct(Album $album)
	{
		$this->album = EmbedAlbumInfo::fromModel($album);
		$this->photos = $album->photos->map(
			fn ($photo) => EmbedPhotoResource::fromModel(
				photo: $photo,
				should_downgrade: !Gate::check(AlbumPolicy::CAN_ACCESS_FULL_PHOTO, [AbstractAlbum::class, $album]),
			));
	}

	/**
	 * Create resource from Album model.
	 *
	 * @param Album $album The album model
	 *
	 * @return self
	 */
	public static function fromModel(Album $album): self
	{
		return new self($album);
	}
}
