<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Traits;

use App\Actions\Map\QueryAlbumMapPoints;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\SizeVariantType;
use App\Http\Controllers\Gallery\AlbumController;
use App\Models\Album;
use App\Models\SizeVariant;
use App\Policies\AlbumPolicy;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\Facades\Gate;

trait HasHeaderUrl
{
	/**
	 * Whether the album header is drawn as the map of its photos (Feature 086,
	 * FR-086-04). When false for a `map` album, {@see self::getHeaderUrl()}
	 * provides the random-photo fallback.
	 */
	protected function isMapHeaderShown(Album $album): bool
	{
		return self::isMapHeaderId($album->header_id) &&
			!request()->configs()->getValueAsBool('use_album_compact_header') &&
			Gate::check(AlbumPolicy::CAN_ACCESS_MAP, [AbstractAlbum::class, $album]) &&
			resolve(QueryAlbumMapPoints::class)->hasPoints($album, request()->configs()->getValueAsBool('map_include_subalbums'));
	}

	/**
	 * `header_id` is a `char(24)`, padded on PostgreSQL.
	 */
	private static function isMapHeaderId(?string $header_id): bool
	{
		return $header_id !== null && trim($header_id) === AlbumController::MAP_HEADER;
	}

	protected function getHeaderUrl(AbstractAlbum $album): ?SizeVariant
	{
		if (request()->configs()->getValueAsBool('use_album_compact_header')) {
			return null;
		}

		if ($album instanceof Album && $album->header_id === AlbumController::COMPACT_HEADER) {
			return null;
		}

		if (!$album->photos()->exists()) {
			return null;
		}

		return $this->getByQuery($album);
	}

	private function getByQuery(AbstractAlbum $album): ?SizeVariant
	{
		$header_size_variant = null;

		if ($album instanceof Album && $album->header_id !== null && !self::isMapHeaderId($album->header_id)) {
			$header_size_variant = SizeVariant::query()
				->where('photo_id', '=', $album->header_id)
				->whereIn('type', [SizeVariantType::MEDIUM2X, SizeVariantType::MEDIUM, SizeVariantType::SMALL2X, SizeVariantType::SMALL])
				->orderBy('type', 'asc')
				->first();
		}

		if ($header_size_variant !== null) {
			return $header_size_variant;
		}

		$photo_id = self::randomPhotoIdWithVariant($album, true) ?? self::randomPhotoIdWithVariant($album, false);

		return $photo_id === null ? null : SizeVariant::query()
			->where('photo_id', '=', $photo_id)
			->where('type', '>', SizeVariantType::ORIGINAL->value)
			->orderBy('type', 'asc')
			->first();
	}

	/**
	 * A random photo of the album visible to the caller with a medium or small
	 * variant (landscape only if `$landscape`), picked in SQL without loading
	 * the album's photos (FR-103-02). Count + random offset rather than
	 * `inRandomOrder()`, which sorts every candidate on MySQL.
	 */
	private static function randomPhotoIdWithVariant(AbstractAlbum $album, bool $landscape): ?string
	{
		$query = $album->photos()->toBase()
			->whereExists(fn (BaseBuilder $query) => $query
				->from('size_variants')
				->whereColumn('size_variants.photo_id', '=', 'photos.id')
				->whereIn('size_variants.type', [SizeVariantType::MEDIUM2X, SizeVariantType::MEDIUM, SizeVariantType::SMALL2X, SizeVariantType::SMALL])
				->when($landscape, fn (BaseBuilder $q) => $q->where('size_variants.ratio', '>', 1)));
		$count = $query->count();

		return $count === 0 ? null : $query->offset(rand(0, $count - 1))->limit(1)->value('photos.id');
	}
}