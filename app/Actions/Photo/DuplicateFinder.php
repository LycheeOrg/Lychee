<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo;

use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Photo;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Look for duplicates in the database.
 */
class DuplicateFinder
{
	/**
	 * Quickly count the number of duplicates candidates.
	 *
	 * @param bool $must_be_within_same_album Requires the duplicates to be in the same album
	 * @param bool $must_have_same_checksum   Requires the duplicates to have the same checksum
	 * @param bool $must_have_same_title      Requires the duplicates to have the same title
	 *
	 * @return int
	 */
	public function checkCount(
		bool $must_be_within_same_album,
		bool $must_have_same_checksum,
		bool $must_have_same_title,
	): int {
		return $this->query($must_be_within_same_album, $must_have_same_checksum, $must_have_same_title)
			->count();
	}

	/**
	 * Return the list of duplicates candidate.
	 *
	 * @param bool $must_be_within_same_album Requires the duplicates to be in the same album
	 * @param bool $must_have_same_checksum   Requires the duplicates to have the same checksum
	 * @param bool $must_have_same_title      Requires the duplicates to have the same title
	 *
	 * @return Collection<int,object{album_id:string,album_title:string,photo_id:string,photo_title:string,checksum:string,short_path:string|null,storage_disk:string|null}>
	 */
	public function search(
		bool $must_be_within_same_album,
		bool $must_have_same_checksum,
		bool $must_have_same_title,
	): Collection {
		/** @var Collection<int,object{album_id:string,album_title:string,photo_id:string,photo_title:string,checksum:string,short_path:string|null,storage_disk:string|null}> */
		return $this->query($must_be_within_same_album, $must_have_same_checksum, $must_have_same_title)
			->get();
	}

	/**
	 * @param bool $must_be_within_same_album Requires the duplicates to be in the same album
	 * @param bool $must_have_same_checksum   Requires the duplicates to have the same checksum
	 * @param bool $must_have_same_title      Requires the duplicates to have the same title
	 *
	 * @return Builder
	 *
	 * @throws LycheeLogicException
	 * @throws QueryBuilderException
	 */
	private function query(
		bool $must_be_within_same_album,
		bool $must_have_same_checksum,
		bool $must_have_same_title,
	): Builder {
		if (!$must_be_within_same_album && !$must_have_same_checksum && !$must_have_same_title) {
			throw new LycheeLogicException('At least one constraint must be enabled.');
		}

		return Photo::query()
			->join('base_albums', 'base_albums.id', '=', 'photos.album_id')
			->join(
				'size_variants', 'size_variants.photo_id', '=', 'photos.id', 'left'
			)
			->whereIn('photos.id', $this->getDuplicatesIdsQuery($must_be_within_same_album, $must_have_same_checksum, $must_have_same_title))
			->where('size_variants.type', '=', 4)
			->select([
				'base_albums.id as album_id',
				'base_albums.title as album_title',
				'photos.id as photo_id',
				'photos.title as photo_title',
				'photos.created_at as photo_created_at',
				'photos.checksum',
				'size_variants.short_path as short_path',
				'size_variants.storage_disk as storage_disk',
			])
			->when($must_have_same_checksum, fn ($q) => $q->orderBy('photos.checksum', 'asc'))
			->when(!$must_have_same_checksum, fn ($q) => $q->orderBy('photos.title', 'asc'))
			->toBase();
	}

	private function getDuplicatesIdsQuery(
		bool $must_be_within_same_album,
		bool $must_have_same_checksum,
		bool $must_have_same_title,
	): Builder {
		return DB::table('photos', 'p1')->select('p1.id')
			->join(
				'photos as p2',
				fn ($join) => $join->on('p1.id', '<>', 'p2.id')
					->when($must_have_same_title, fn ($q) => $q->on('p1.title', '=', 'p2.title'))
					->when($must_have_same_checksum, fn ($q) => $q->on('p1.checksum', '=', 'p2.checksum'))
					->when($must_be_within_same_album, fn ($q) => $q->on('p1.album_id', '=', 'p2.album_id'))
			);
	}
}
