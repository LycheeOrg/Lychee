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
use App\Models\SizeVariant;

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

		if ($album->photos->isEmpty()) {
			return null;
		}

		// TODO : already use the prefetched data for photos instead of 2 extra queries?

		return $this->getByQuery($album);
	}

	private function getByQuery(AbstractAlbum $album): ?string
	{
		$headerSizeVariant = null;

		if ($album instanceof Album && $album->header_id !== null) {
			$headerSizeVariant = SizeVariant::query()
				->where('photo_id', '=', $album->header_id)
				->whereIn('type', [SizeVariantType::MEDIUM, SizeVariantType::SMALL2X, SizeVariantType::SMALL])
				->orderBy('type', 'asc')
				->first();
		}

		if ($headerSizeVariant !== null) {
			return $headerSizeVariant->url;
		}

		$query_ratio = SizeVariant::query()
					->select('photo_id')
					->whereBelongsTo($album->photos)
					->where('ratio', '>', 1) // ! we prefer landscape first.
					->whereIn('type', [SizeVariantType::MEDIUM, SizeVariantType::SMALL2X, SizeVariantType::SMALL]);
		$num = $query_ratio->count() - 1;
		$photo = $query_ratio->skip(rand(0, $num))->first();

		if ($photo === null) {
			$query = SizeVariant::query()
				->select('photo_id')
				->whereBelongsTo($album->photos)
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