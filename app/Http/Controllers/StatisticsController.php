<?php

namespace App\Http\Controllers;

use App\Actions\Statistics\Spaces;
use App\Http\Requests\Statistics\SpacePerAlbumRequest;
use App\Http\Requests\Statistics\SpacePerUserRequest;
use App\Http\Requests\Statistics\SpaceSizeVariantRequest;
use App\Http\Resources\Statistics\Album;
use App\Http\Resources\Statistics\Sizes;
use App\Http\Resources\Statistics\UserSpace;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

class StatisticsController extends Controller
{
	/**
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

		$zipped = $spaceData->zip($countData);

		return $zipped->map(fn ($z) => new Album($z[0], $z[1]));
	}

	/**
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

		$zipped = $spaceData->zip($countData);

		return $zipped->map(fn ($z) => new Album($z[0], $z[1]));
	}
}
