<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Contracts\Models\AbstractAlbum;
use App\Exceptions\PhotoCollectionEmptyException;
use App\Http\Requests\Frame\FrameRequest;
use App\Http\Resources\Frame\FrameData;
use App\Http\Resources\Models\PhotoResource;
use App\Models\Configs;
use App\Models\Photo;
use App\Policies\PhotoQueryPolicy;
use Illuminate\Routing\Controller;

class FrameController extends Controller
{
	public function __construct(protected PhotoQueryPolicy $photo_query_policy)
	{
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
		$timeout = Configs::getValueAsInt('mod_frame_refresh');
		$photo = $this->loadPhoto($request->album(), 5);

		if ($photo === null) {
			return new FrameData($timeout, '', '');
		}

		$src = $photo->size_variants->getMedium()?->url ?? $photo->size_variants->getOriginal()?->url;

		if ($photo->size_variants->getMedium() !== null && $photo->size_variants->getMedium2x() !== null) {
			$srcset = $photo->size_variants->getMedium()->url . ' ' . $photo->size_variants->getMedium()->width . 'w,';
			$srcset .= $photo->size_variants->getMedium2x()->url . ' ' . $photo->size_variants->getMedium2x()->width . 'w';
		} else {
			$srcset = '';
		}

		return new FrameData($timeout, $src, $srcset);
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
		$photo = $this->loadPhoto($request->album(), 5);

		return new PhotoResource($photo, $request->album());
	}

	/**
	 * Recursively search for a photo to display.
	 *
	 * @param AbstractAlbum|null $album
	 * @param int                $retries
	 *
	 * @return Photo|null
	 */
	private function loadPhoto(AbstractAlbum|null $album, int $retries = 5): ?Photo
	{
		// avoid infinite recursion
		if ($retries === 0) {
			return null;
		}

		$query = null;

		// default query
		if ($album === null) {
			$query = $this->photo_query_policy->applySearchabilityFilter(
				query: Photo::query()->with(['albums', 'size_variants', 'palette']),
				origin: null,
				include_nsfw: !Configs::getValueAsBool('hide_nsfw_in_frame')
			);
		} else {
			$query = $album->photos()->with(['albums', 'size_variants', 'palette']);
		}

		/** @var ?Photo $photo */
		$photo = $query->inRandomOrder()->first();
		if ($photo === null) {
			$album === null ? throw new PhotoCollectionEmptyException() : throw new PhotoCollectionEmptyException('Photo collection of ' . $album->get_title() . ' is empty');
		}

		// retry
		if ($photo->isVideo()) {
			return $this->loadPhoto($album, $retries - 1);
		}

		return $photo;
	}
}