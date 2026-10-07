<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers;

use App\Actions\Statistics\Spaces;
use App\Http\Requests\Statistics\SpacePerAlbumRequest;
use App\Http\Requests\Statistics\SpaceSizeVariantRequest;
use App\Http\Resources\Statistics\Album;
use App\Http\Resources\Statistics\Sizes;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

class StatisticsController extends Controller
{
	/**
	 * Fetch the used space per SizeVariant type.
	 *
	 * @return Collection<int,Sizes>
	 */
	public function getSpacePerSizeVariantType(SpaceSizeVariantRequest $request, Spaces $spaces): Collection
	{
		$album_id = $request->album()?->get_id();
		$owner_id = $album_id === null ? $request->ownerId() : null;

		$space_data = $album_id === null
			? $spaces->getSpacePerSizeVariantTypePerUser(owner_id: $owner_id)
			: $spaces->getSpacePerSizeVariantTypePerAlbum(album_id: $album_id);

		return Sizes::collect($space_data);
	}

	/**
	 * Fetch the used space and number of photos per Album (without descendants).
	 *
	 * @return Collection<int,Album>
	 */
	public function getSpacePerAlbum(SpacePerAlbumRequest $request, Spaces $spaces): Collection
	{
		$album_id = $request->album()?->get_id();
		$owner_id = $album_id === null ? $request->ownerId() : null;
		$space_data = $spaces->getSpacePerAlbum(
			album_id: $album_id,
			owner_id: $owner_id
		);
		$count_data = $spaces->getPhotoCountPerAlbum(
			album_id: $album_id,
			owner_id: $owner_id);

		/** Collection<int,array{0:array{id:string,left:int,right:int,size:int},1:array{id:string,username:string,title:string,is_nsfw:bool,left:int,right:int,num_photos:int,num_descendants:int}}> $zipped */
		$zipped = $space_data->zip($count_data);

		return $zipped->map(fn ($z) => new Album($z[0], $z[1]));
	}

	/**
	 * Fetch the used space and number of photos per Album with descendants
	 * ! Slow query.
	 *
	 * @return Collection<int,Album>
	 */
	public function getTotalSpacePerAlbum(SpacePerAlbumRequest $request, Spaces $spaces): Collection
	{
		$album_id = $request->album()?->get_id();
		$owner_id = $album_id === null ? $request->ownerId() : null;
		$space_data = $spaces->getTotalSpacePerAlbum(
			album_id: $album_id,
			owner_id: $owner_id
		);
		$count_data = $spaces->getTotalPhotoCountPerAlbum(
			album_id: $album_id,
			owner_id: $owner_id);
		/** Collection<int,array{0:array{id:string,left:int,right:int,size:int},1:array{id:string,username:string,title:string,is_nsfw:bool,left:int,right:int,num_photos:int,num_descendants:int}}> $zipped */
		$zipped = $space_data->zip($count_data);

		return $zipped->map(fn ($z) => new Album($z[0], $z[1]));
	}
}
