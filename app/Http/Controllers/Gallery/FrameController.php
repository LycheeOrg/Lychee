<?php

namespace App\Http\Controllers\Gallery;

use App\Exceptions\PhotoCollectionEmptyException;
use App\Factories\AlbumFactory;
use App\Http\Requests\Frame\FrameRequest;
use App\Http\Resources\Frame\FrameData;
use App\Models\Configs;
use App\Models\Photo;
use App\Policies\PhotoQueryPolicy;

class FrameController
{
	private PhotoQueryPolicy $photoQueryPolicy;
	private AlbumFactory $albumFactory;
	private int $timeout;

	public function __construct()
	{
		$this->photoQueryPolicy = resolve(PhotoQueryPolicy::class);
		$this->albumFactory = resolve(AlbumFactory::class);
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
		$this->timeout = Configs::getValueAsInt('mod_frame_refresh');

		return $this->loadPhoto($request->albumId(), 5);
	}

	/**
	 * Recursively search for a photo to display.
	 *
	 * @param string|null $albumId
	 * @param int         $retries
	 *
	 * @return FrameData
	 */
	private function loadPhoto(string|null $albumId, int $retries = 5): FrameData
	{
		$src = '';
		$srcset = '';

		// avoid infinite recursion
		if ($retries === 0) {
			return new FrameData($this->timeout, '', '');
		}

		// default query
		$query = $this->photoQueryPolicy->applySearchabilityFilter(Photo::query()->with(['album', 'size_variants', 'size_variants.sym_links']));

		if ($albumId !== null) {
			$query = $this->albumFactory->findAbstractAlbumOrFail($albumId)
									 ->photos()
									 ->with(['album', 'size_variants', 'size_variants.sym_links']);
		}

		/** @var ?Photo $photo */
		// PHPStan does not understand that `firstOrFail` returns `Photo`, but assumes that it returns `Model`
		// @phpstan-ignore-next-line
		$photo = $query->inRandomOrder()->first();
		if ($photo === null) {
			$albumId === null ? throw new PhotoCollectionEmptyException() : throw new PhotoCollectionEmptyException('Photo collection of ' . $albumId . ' is empty');
		}

		// retry
		if ($photo->isVideo()) {
			return $this->loadPhoto($albumId, $retries - 1);
		}

		$src = $photo->size_variants->getMedium()?->url ?? $photo->size_variants->getOriginal()?->url;

		if ($photo->size_variants->getMedium() !== null && $photo->size_variants->getMedium2x() !== null) {
			$srcset = $photo->size_variants->getMedium()->url . ' ' . $photo->size_variants->getMedium()->width . 'w';
			$srcset .= $photo->size_variants->getMedium2x()->url . ' ' . $photo->size_variants->getMedium2x()->width . 'w';
		} else {
			$srcset = '';
		}

		return new FrameData($this->timeout, $src, $srcset);
	}
}