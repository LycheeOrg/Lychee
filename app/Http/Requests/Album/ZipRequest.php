<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasAlbums;
use App\Contracts\Http\Requests\HasPhotos;
use App\Contracts\Http\Requests\HasSizeVariant;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\DownloadVariantType;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAlbumsTrait;
use App\Http\Requests\Traits\HasPhotosTrait;
use App\Http\Requests\Traits\HasSizeVariantTrait;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use App\Rules\AlbumIDListRule;
use App\Rules\RandomIDListRule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;

/**
 * @implements HasAlbums<AbstractAlbum>
 */
class ZipRequest extends BaseApiRequest implements HasAlbums, HasPhotos, HasSizeVariant
{
	/** @use HasAlbumsTrait<AbstractAlbum> */
	use HasAlbumsTrait;
	use HasPhotosTrait;
	use HasSizeVariantTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		/** @var AbstractAlbum $album */
		foreach ($this->albums as $album) {
			if (!Gate::check(AlbumPolicy::CAN_DOWNLOAD, $album)) {
				return false;
			}
		}

		/** @var Photo $photo */
		foreach ($this->photos as $photo) {
			if (!Gate::check(PhotoPolicy::CAN_DOWNLOAD, $photo)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_IDS_ATTRIBUTE => ['sometimes', new AlbumIDListRule()],
			RequestAttribute::PHOTO_IDS_ATTRIBUTE => ['sometimes', new RandomIDListRule()],
			RequestAttribute::SIZE_VARIANT_ATTRIBUTE => ['required_if_accepted:photos_ids', new Enum(DownloadVariantType::class)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$album_ids = $values[RequestAttribute::ALBUM_IDS_ATTRIBUTE] ?? null;
		$album_ids = $album_ids === null ? [] : explode(',', $album_ids);
		$this->processAlbums($album_ids);

		// only interesting if we have no albums
		$this->sizeVariant = DownloadVariantType::tryFrom($values[RequestAttribute::SIZE_VARIANT_ATTRIBUTE] ?? '');

		$photo_ids = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE] ?? null;
		$photo_ids = $photo_ids === null ? [] : explode(',', $photo_ids);
		$this->processPhotos($photo_ids);
	}

	/**
	 * Process albums. Set to empty collection if no albums are requested.
	 *
	 * @param string[] $album_ids
	 *
	 * @return void
	 */
	private function processAlbums(array $album_ids): void
	{
		if (count($album_ids) === 0) {
			$this->albums = collect();

			return;
		}

		// TODO: `App\Actions\Album\Archive::compressAlbum` iterates over the original size variant of each photo in the album; we should eagerly load them for higher efficiency.
		$this->albums = $this->albumFactory->findAbstractAlbumsOrFail($album_ids);
	}

	/**
	 * Process photos. Set to empty collection if no photos are requested.
	 *
	 * @param string[] $photo_ids
	 *
	 * @return void
	 */
	private function processPhotos(array $photo_ids): void
	{
		if (count($photo_ids) === 0) {
			$this->photos = collect();

			return;
		}

		$photoQuery = Photo::query()->with(['album']);
		// The condition is required, because Lychee also supports to archive
		// the "live video" as a size variant which is not a proper size variant
		$variant = $this->sizeVariant->getSizeVariantType();
		if ($variant !== null) { // NOT LIVE PHOTO
			// If a proper size variant is requested, eagerly load the size
			// variants but only the requested type due to efficiency reasons
			$photoQuery = $photoQuery->with([
				'size_variants' => fn ($r) => $r->where('type', '=', $variant),
			]);
		}

		// `findOrFail` returns the union `Photo|Collection<int,Photo>`
		// which is not assignable to `Collection<int,Photo>`; but as we query
		// with an array of IDs we never get a single entity (even if the
		// array only contains a single ID).
		$this->photos = $photoQuery->findOrFail($photo_ids);
	}
}
