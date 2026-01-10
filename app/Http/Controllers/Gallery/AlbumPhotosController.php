<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Contracts\Models\AbstractAlbum;
use App\Enum\TimelinePhotoGranularity;
use App\Http\Requests\Album\GetAlbumPhotosRequest;
use App\Http\Resources\Collections\PaginatedPhotosResource;
use App\Models\Album;
use App\Models\TagAlbum;
use App\Repositories\ConfigManager;
use App\Repositories\PhotoRepository;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;

/**
 * Controller for returning paginated photos.
 * Used by the pagination feature - frontend loads album metadata via /Album::head,
 * then fetches paginated photos via this endpoint.
 */
class AlbumPhotosController extends Controller
{
	public function __construct(private PhotoRepository $photo_repository)
	{
	}

	/**
	 * Get paginated photos.
	 *
	 * @param GetAlbumPhotosRequest $request the request with validated album_id and page
	 *
	 * @return PaginatedPhotosResource paginated list of photos with metadata
	 */
	public function get(GetAlbumPhotosRequest $request): PaginatedPhotosResource
	{
		$album = $request->album();

		$per_page = $request->configs()->getValueAsInt('photos_per_page');

		if ($album instanceof BaseSmartAlbum) {
			$config_manager = resolve(ConfigManager::class);

			return new PaginatedPhotosResource(
				paginated_photos: $album->getPhotos(),
				album_id: $album->get_id(),
				should_downgrade: !$config_manager->getValueAsBool('grants_full_photo_access'),
				photo_timeline: $config_manager->getValueAsEnum('timeline_photos_granularity', TimelinePhotoGranularity::class),
			);
		}
		// grants_full_photo_access
		if ($album instanceof TagAlbum) {
			$config_manager = resolve(ConfigManager::class);

			return new PaginatedPhotosResource(
				paginated_photos: $album->photos()->paginate($config_manager->getValueAsInt('photos_per_page')),
				album_id: $album->id,
				should_downgrade: Gate::check(\AlbumPolicy::CAN_ACCESS_FULL_PHOTO, [AbstractAlbum::class, $album]) === false,
				photo_timeline: $album->photo_timeline,
			);
		}

		/** @var Album $album */
		$paginator = $this->photo_repository->getPhotosForAlbumPaginated($album->id, $album->getEffectivePhotoSorting(), $per_page);

		return new PaginatedPhotosResource(
			paginated_photos: $paginator,
			album_id: $album->id,
			should_downgrade: Gate::check(\AlbumPolicy::CAN_ACCESS_FULL_PHOTO, [AbstractAlbum::class, $album]) === false,
			photo_timeline: $album->photo_timeline);
	}
}
