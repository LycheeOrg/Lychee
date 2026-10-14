<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Actions\Frame\ListBrowsableAlbumTitles;
use App\Contracts\Models\AbstractAlbum;
use App\Exceptions\PhotoCollectionEmptyException;
use App\Http\Requests\Frame\FrameRequest;
use App\Http\Resources\Frame\FrameClockData;
use App\Http\Resources\Frame\FrameData;
use App\Http\Resources\Frame\FrameInfoData;
use App\Http\Resources\Frame\FramePhotoData;
use App\Http\Resources\Models\PhotoResource;
use App\Models\Album;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use App\Policies\PhotoQueryPolicy;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class FrameController extends Controller
{
	public function __construct(
		protected PhotoQueryPolicy $photo_query_policy,
		protected ListBrowsableAlbumTitles $list_browsable_album_titles,
	) {
	}

	/**
	 * Return an image and the timeout if the frame is supported.
	 *
	 * @param FrameRequest $request
	 *
	 * @return FrameData
	 */
	public function get(FrameRequest $request): FrameData
	{
		$timeout = $request->configs()->getValueAsInt('mod_frame_refresh');
		$clock = FrameClockData::fromConfigs($request->configs());
		$info = FrameInfoData::fromConfigs($request->configs());
		$photo = $this->loadPhoto($request->album(), $request->subAlbumRoot(), 5);

		if ($photo === null) {
			return new FrameData($timeout, '', '', $clock, $info, null);
		}

		$src = $photo->size_variants->getMedium()?->url ?? $photo->size_variants->getOriginal()?->url;

		if ($photo->size_variants->getMedium() !== null && $photo->size_variants->getMedium2x() !== null) {
			$srcset = $photo->size_variants->getMedium()->url . ' ' . $photo->size_variants->getMedium()->width . 'w,';
			$srcset .= $photo->size_variants->getMedium2x()->url . ' ' . $photo->size_variants->getMedium2x()->width . 'w';
		} else {
			$srcset = '';
		}

		return new FrameData($timeout, $src, $srcset, $clock, $info, $this->photoData($request, $photo));
	}

	/**
	 * Photo information for the info box, under the photo view's privacy rules (FR-088-05, FR-088-07).
	 */
	private function photoData(FrameRequest $request, Photo $photo): FramePhotoData
	{
		$configs = $request->configs();

		return FramePhotoData::fromPhoto(
			photo: $photo,
			albums: $request->offersAlbumLine() ? $this->list_browsable_album_titles->do($photo) : null,
			is_exif_visible: $configs->getValueAsBool('display_exif_data'),
			is_title_visible: !($configs->getValueAsBool('file_name_hidden') && Auth::guest()),
			taken_at_format: $configs->getValueAsString('date_format_photo_overlay'),
		);
	}

	/**
	 * Return the full random image data instead of just the URLs.
	 *
	 * @param FrameRequest $request
	 *
	 * @return PhotoResource
	 */
	public function random(FrameRequest $request): PhotoResource
	{
		$photo = $this->loadPhoto($request->album(), null, 5);

		return new PhotoResource(
			photo: $photo,
			album_id: $request->album()?->get_id(),
			should_downgrade_size_variants: !Gate::check(PhotoPolicy::CAN_ACCESS_FULL_PHOTO, [Photo::class, $photo]),
		);
	}

	/**
	 * Recursively search for a photo to display.
	 *
	 * @param AbstractAlbum|null $album
	 * @param Album|null         $sub_album_root the album whose whole tree is drawn from in sub-album mode (FR-088-16)
	 * @param int                $retries
	 *
	 * @return Photo|null
	 */
	private function loadPhoto(AbstractAlbum|null $album, ?Album $sub_album_root, int $retries = 5): ?Photo
	{
		// avoid infinite recursion
		if ($retries === 0) {
			return null;
		}

		$query = null;

		// default query
		if ($album === null) {
			$user = Auth::user();
			$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();

			$query = $this->photo_query_policy->applySearchabilityFilter(
				query: Photo::query()->with(['albums.access_permissions', 'size_variants', 'palette', 'tags', 'rating']),
				user: $user,
				unlocked_album_ids: $unlocked_album_ids,
				origin: null,
				include_nsfw: !request()->configs()->getValueAsBool('hide_nsfw_in_frame')
			);
		} elseif ($sub_album_root !== null) {
			// all_photos() orders by the album's photo sorting: reorder() leaves the order to inRandomOrder().
			$include_nsfw = !request()->configs()->getValueAsBool('hide_nsfw_in_frame');
			$query = $sub_album_root->all_photos($include_nsfw)->getQuery()->with(['albums.access_permissions', 'size_variants', 'palette', 'tags', 'rating'])->reorder();
		} else {
			$query = $album->photos()->with(['albums.access_permissions', 'size_variants', 'palette', 'tags', 'rating']);
		}

		/** @var ?Photo $photo */
		$photo = $query->inRandomOrder()->first();
		if ($photo === null && $album === null) {
			throw new PhotoCollectionEmptyException();
		}
		if ($photo === null && $album !== null) {
			throw new PhotoCollectionEmptyException('Photo collection of ' . $album->get_title() . ' is empty');
		}

		// retry
		if ($photo->isVideo()) {
			return $this->loadPhoto($album, $sub_album_root, $retries - 1);
		}

		return $photo;
	}
}