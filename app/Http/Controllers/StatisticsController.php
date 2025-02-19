<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers;

use App\Actions\Statistics\Counts;
use App\Actions\Statistics\Spaces;
use App\Enum\CountType;
use App\Http\Requests\Statistics\CountsRequest;
use App\Http\Requests\Statistics\SpacePerAlbumRequest;
use App\Http\Requests\Statistics\SpacePerUserRequest;
use App\Http\Requests\Statistics\SpaceSizeVariantRequest;
use App\Http\Resources\Statistics\Album;
use App\Http\Resources\Statistics\CountsData;
use App\Http\Resources\Statistics\Sizes;
use App\Http\Resources\Statistics\UserSpace;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

class StatisticsController extends Controller
{
	/**
	 * Fetch the used space per user.
	 *
	 * @param SpacePerUserRequest $request
	 * @param Spaces              $spaces
	 *
	 * @return Collection<int,UserSpace>
	 */
	public function getSpacePerUser(SpacePerUserRequest $request, Spaces $spaces): Collection
	{
		$spaceData = $spaces->getFullSpacePerUser(
			owner_id: $request->ownerId()
		);

		return UserSpace::collect($spaceData);
	}

	/**
	 * Fetch the used space per SizeVariant type.
	 *
	 * @param SpaceSizeVariantRequest $request
	 * @param Spaces                  $spaces
	 *
	 * @return Collection<int,Sizes>
	 */
	public function getSpacePerSizeVariantType(SpaceSizeVariantRequest $request, Spaces $spaces): Collection
	{
		$albumId = $request->album()?->id;
		$ownerId = $albumId === null ? $request->ownerId() : null;

		$spaceData = $albumId === null
			? $spaces->getSpacePerSizeVariantTypePerUser(owner_id: $ownerId)
			: $spaces->getSpacePerSizeVariantTypePerAlbum(album_id: $albumId);

		return Sizes::collect($spaceData);
	}

	/**
	 * Fetch the used space and number of photos per Album (without descendants).
	 *
	 * @param SpacePerAlbumRequest $request
	 * @param Spaces               $spaces
	 *
	 * @return Collection<int,Album>
	 */
	public function getSpacePerAlbum(SpacePerAlbumRequest $request, Spaces $spaces): Collection
	{
		$albumId = $request->album()?->id;
		$ownerId = $albumId === null ? $request->ownerId() : null;
		$spaceData = $spaces->getSpacePerAlbum(
			album_id: $albumId,
			owner_id: $ownerId
		);
		$countData = $spaces->getPhotoCountPerAlbum(
			album_id: $albumId,
			owner_id: $ownerId);

		/** @var Collection<int,array{0:array{id:string,left:int,right:int,size:int},1:array{id:string,username:string,title:string,is_nsfw:bool,left:int,right:int,num_photos:int,num_descendants:int}}> $zipped */
		$zipped = $spaceData->zip($countData);

		return $zipped->map(fn ($z) => new Album($z[0], $z[1]));
	}

	/**
	 * Fetch the used space and number of photos per Album with descendants
	 * ! Slow query.
	 *
	 * @param SpacePerAlbumRequest $request
	 * @param Spaces               $spaces
	 *
	 * @return Collection<int,Album>
	 */
	public function getTotalSpacePerAlbum(SpacePerAlbumRequest $request, Spaces $spaces): Collection
	{
		$albumId = $request->album()?->id;
		$ownerId = $albumId === null ? $request->ownerId() : null;
		$spaceData = $spaces->getTotalSpacePerAlbum(
			album_id: $albumId,
			owner_id: $ownerId
		);
		$countData = $spaces->getTotalPhotoCountPerAlbum(
			album_id: $albumId,
			owner_id: $ownerId);

		/** @var Collection<int,array{0:array{id:string,left:int,right:int,size:int},1:array{id:string,username:string,title:string,is_nsfw:bool,left:int,right:int,num_photos:int,num_descendants:int}}> $zipped */
		$zipped = $spaceData->zip($countData);

		return $zipped->map(fn ($z) => new Album($z[0], $z[1]));
	}

	/**
	 * Fetch the number of uploads/taken_at over time.
	 *
	 * @param CountsRequest $request
	 * @param Counts        $counts  Dependency injection
	 *
	 * @return CountsData
	 */
	public function getPhotoCountOverTime(CountsRequest $request, Counts $counts): CountsData
	{
		if ($request->type === CountType::TAKEN_AT) {
			$data = $counts->getTakenAtCountOverTime(
				$request->ownerId(),
				min_date: $request->min,
				max_date: $request->max,
			);
		} else {
			$data = $counts->getCreatedAtCountOverTime(
				$request->ownerId(),
				min_date: $request->min,
				max_date: $request->max,
			);
		}

		$min_taken_at = $counts->getMinTakenAt($request->ownerId());
		$min_created_at = $counts->getMinCreatedAt($request->ownerId());

		return new CountsData($data, $min_taken_at, $min_created_at);
	}
}
