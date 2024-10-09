<?php

namespace App\Http\Controllers;

use App\Actions\Statistics\Spaces;
use App\Http\Requests\Statistics\SpacePerAlbumRequest;
use App\Http\Requests\Statistics\SpacePerUserRequest;
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
	 * @param SpacePerUserRequest $request
	 * @param Spaces              $spaces
	 *
	 * @return Collection<int,Sizes>
	 */
	public function getSpacePerSizeVariantType(SpacePerUserRequest $request, Spaces $spaces): Collection
	{
		$spaceData = $spaces->getSpacePerSizeVariantType(
			owner_id: $request->ownerId()
		);

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

		return Album::collect($spaceData);
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

		return Album::collect($spaceData);
	}
}
