<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Resources\Collections;

use App\Legacy\V1\Resources\Models\AlbumResource;
use App\Legacy\V1\Resources\Models\SmartAlbumResource;
use App\Legacy\V1\Resources\Models\TagAlbumResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * Data Transfer Object (DTO) to transmit the top albums to the client.
 *
 * This DTO differentiates between albums which are owned by the user and
 * "shared" albums which the user does not own, but is allowed to see.
 * The term "shared album" might be a little misleading here.
 * Albums which are owned by the user himself may also be shared (with
 * other users.)
 * Actually, in this context "shared albums" means "foreign albums".
 */
final class TopAlbumsResource extends JsonResource
{
	/**
	 * @param Collection<int,\App\SmartAlbums\BaseSmartAlbum> $smart_albums
	 * @param Collection<int,\App\Models\TagAlbum>            $tag_albums
	 * @param Collection<int,\App\Models\Album>               $albums
	 * @param Collection<int,\App\Models\Album>|null          $shared_albums
	 *
	 * @return void
	 */
	public function __construct(
		public Collection $smart_albums,
		public Collection $tag_albums,
		public Collection $albums,
		public ?Collection $shared_albums = null,
	) {
		// Laravel applies a shortcut when this value === null but not when it is something else.
		parent::__construct('must_not_be_null');

		$this->shared_albums ??= new Collection();
	}

	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array<string,mixed>|\Illuminate\Contracts\Support\Arrayable<string,mixed>|\JsonSerializable
	 */
	public function toArray($request)
	{
		return [
			'smart_albums' => SmartAlbumResource::collection($this->smart_albums),
			'tag_albums' => TagAlbumResource::collection($this->tag_albums),
			'albums' => AlbumResource::collection($this->albums),
			'shared_albums' => AlbumResource::collection($this->shared_albums),
		];
	}
}