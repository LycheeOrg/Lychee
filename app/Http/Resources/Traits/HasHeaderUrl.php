<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Traits;

use App\Contracts\Models\AbstractAlbum;
use App\Enum\SizeVariantType;
use App\Http\Controllers\Gallery\AlbumController;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

trait HasHeaderUrl
{
	protected function getHeaderUrl(AbstractAlbum $album): ?string
	{
		if (Configs::getValueAsBool('use_album_compact_header')) {
			return null;
		}

		if ($album instanceof Album && $album->header_id === AlbumController::COMPACT_HEADER) {
			return null;
		}

		if ($album->get_photos()->isEmpty()) {
			return null;
		}

		// TODO : already use the prefetched data for photos instead of 2 extra queries?
		return $this->getByQuery($album);
	}

	private function getByQuery(AbstractAlbum $album): ?string
	{
		$header_size_variant = null;

		if ($album instanceof Album && $album->header_id !== null) {
			$header_size_variant = SizeVariant::query()
				->where('photo_id', '=', $album->header_id)
				->whereIn('type', [SizeVariantType::MEDIUM, SizeVariantType::SMALL2X, SizeVariantType::SMALL])
				->orderBy('type', 'asc')
				->first();
		}

		if ($header_size_variant !== null) {
			return $header_size_variant->url;
		}

		$query_ratio = SizeVariant::query()->select('photo_id');

		/** @var Collection<int,Photo>|LengthAwarePaginator<int,Photo> $photos */
		$photos = $album->get_photos();
		if ($photos instanceof LengthAwarePaginator) {
			$photo_ids = collect($photos->items())->pluck('id')->all();
			$query_ratio = $query_ratio->whereIn('photo_id', $photo_ids);
		} else {
			$query_ratio = $query_ratio->whereBelongsTo($photos);
		}
		$query_ratio = $query_ratio->where('ratio', '>', 1)->whereIn('type', [SizeVariantType::MEDIUM, SizeVariantType::SMALL2X, SizeVariantType::SMALL]);
		$num = $query_ratio->count() - 1;
		$photo = $query_ratio->skip(rand(0, $num))->first();

		if ($photo === null) {
			$query = SizeVariant::query()
				->select('photo_id')
				->whereBelongsTo($album->get_photos())
				->whereIn('type', [SizeVariantType::MEDIUM, SizeVariantType::SMALL2X, SizeVariantType::SMALL]);
			$num = $query->count() - 1;
			$photo = $query->skip(rand(0, $num))->first();
		}

		return $photo === null ? null : SizeVariant::query()
			->where('photo_id', '=', $photo->photo_id)
			->where('type', '>', 1)
			->orderBy('type', 'asc')
			->first()?->url;
	}
}