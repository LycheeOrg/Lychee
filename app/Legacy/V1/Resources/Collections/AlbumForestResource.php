<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Resources\Collections;

use App\Legacy\V1\Resources\Models\AlbumTreeResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * Resource returned when querying for the full tree of acccessible albums.
 */
final class AlbumForestResource extends JsonResource
{
	/**
	 * @param Collection<int,\App\Contracts\Models\AbstractAlbum>      $albums
	 * @param Collection<int,\App\Contracts\Models\AbstractAlbum>|null $shared_albums
	 *
	 * @return void
	 */
	public function __construct(
		public Collection $albums,
		public ?Collection $shared_albums = null,
	) {
		// Laravel applies a shortcut when this value === null but not when it is something else.
		parent::__construct('must_not_be_null');

		$this->albums = $albums;
		$this->shared_albums = $shared_albums ?? new Collection();
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
			'albums' => AlbumTreeResource::collection($this->albums),
			'shared_albums' => AlbumTreeResource::collection($this->shared_albums),
		];
	}
}