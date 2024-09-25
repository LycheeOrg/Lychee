<?php

namespace App\Http\Controllers\Gallery;

use App\Contracts\Models\AbstractAlbum;
use App\Exceptions\PhotoCollectionEmptyException;
use App\Http\Requests\Frame\FrameRequest;
use App\Http\Resources\Frame\FrameData;
use App\Models\Configs;
use App\Models\Photo;
use App\Policies\PhotoQueryPolicy;

class FrameController
{
	private PhotoQueryPolicy $photoQueryPolicy;

	public function __construct()
	{
		$this->photoQueryPolicy = resolve(PhotoQueryPolicy::class);
	}

	/**
	 * Return an image and the timeout if the frame is supported..
	 *
	 * @param FrameRequest $request
	 *
	 * @return FrameData
	 */
	public function get(FrameRequest $request): FrameData
	{
		return $this->loadPhoto($request->album(), 5);
	}

	/**
	 * Recursively search for a photo to display.
	 *
	 * @param AbstractAlbum|null $album
	 * @param int                $retries
	 *
	 * @return FrameData
	 */
	private function loadPhoto(AbstractAlbum|null $album, int $retries = 5): FrameData
	{
		$src = '';
		$srcset = '';

		// avoid infinite recursion
		if ($retries === 0) {
			$timeout = Configs::getValueAsInt('mod_frame_refresh');

			return new FrameData($timeout, '', '');
		}

		// default query
		$query = $this->photoQueryPolicy->applySearchabilityFilter(Photo::query()->with(['album', 'size_variants', 'size_variants.sym_links']));

		if ($album !== null) {
			$query = $album->photos()->with(['album', 'size_variants', 'size_variants.sym_links']);
		}

		/** @var ?Photo $photo */
		// PHPStan does not understand that `firstOrFail` returns `Photo`, but assumes that it returns `Model`
		// @phpstan-ignore-next-line
		$photo = $query->inRandomOrder()->first();
		if ($photo === null) {
			$album === null ? throw new PhotoCollectionEmptyException() : throw new PhotoCollectionEmptyException('Photo collection of ' . $album->title . ' is empty');
		}

		// retry
		if ($photo->isVideo()) {
			return $this->loadPhoto($album, $retries - 1);
		}

		$src = $photo->size_variants->getMedium()?->url ?? $photo->size_variants->getOriginal()?->url;

		if ($photo->size_variants->getMedium() !== null && $photo->size_variants->getMedium2x() !== null) {
			$srcset = $photo->size_variants->getMedium()->url . ' ' . $photo->size_variants->getMedium()->width . 'w';
			$srcset .= $photo->size_variants->getMedium2x()->url . ' ' . $photo->size_variants->getMedium2x()->width . 'w';
		} else {
			$srcset = '';
		}

		$timeout = Configs::getValueAsInt('mod_frame_refresh');

		return new FrameData($timeout, $src, $srcset);
	}
}