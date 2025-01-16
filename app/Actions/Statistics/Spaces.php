<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Statistics;

use App\Enum\SizeVariantType;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * We compute the size usage from the size variants.
 * Do note that this number may be slightly off due to the way we store pictures in the database:
 * row are duplicates for pictures, but the file is stored only once.
 */
class Spaces
{
	/**
	 * Return the amount of data stored on the server (optionally for a user).
	 *
	 * @param int|null $owner_id
	 *
	 * @return Collection<int,array{id:int,username:string,size:int}>
	 */
	public function getFullSpacePerUser(?int $owner_id = null): Collection
	{
		return DB::table('users')
			->when($owner_id !== null, fn ($query) => $query->where('users.id', '=', $owner_id))
			->joinSub(
				query: DB::table('photos')->select(['photos.id', 'photos.owner_id']),
				as: 'photos',
				first: 'photos.owner_id',
				operator: '=',
				second: 'users.id',
				type: 'left'
			)
			->joinSub(
				query: DB::table('size_variants')
					->select(['size_variants.photo_id', 'size_variants.filesize'])
					->where('size_variants.type', '!=', 7),
				as: 'size_variants',
				first: 'size_variants.photo_id',
				operator: '=',
				second: 'photos.id',
				type: 'left'
			)
			->select(
				'users.id',
				'username',
				DB::raw('SUM(size_variants.filesize) as size')
			)
			->groupBy('users.id', 'username')
			->orderBy('users.id', 'asc')
			->get()
			->map(fn ($item) => [
				'id' => intval($item->id),
				'username' => strval($item->username),
				'size' => intval($item->size),
			]);
	}

	/**
	 * Return the amount of data stored on the server (optionally for a user).
	 *
	 * @param int|null $owner_id
	 *
	 * @return Collection<int,array{type:SizeVariantType,size:int}>
	 */
	public function getSpacePerSizeVariantTypePerUser(?int $owner_id = null): Collection
	{
		return DB::table('size_variants')
			->when($owner_id !== null, fn ($query) => $query
				->joinSub(
					query: DB::table('photos')->select(['photos.id', 'photos.owner_id']),
					as: 'photos',
					first: 'photos.id',
					operator: '=',
					second: 'size_variants.photo_id'
				)
				->where('photos.owner_id', '=', $owner_id))
			->select(
				'size_variants.type',
				DB::raw('SUM(size_variants.filesize) as size')
			)
			->where('size_variants.type', '!=', 7)
			->groupBy('size_variants.type')
			->orderBy('size_variants.type', 'asc')
			->get()
			->map(fn ($item) => [
				'type' => SizeVariantType::from($item->type),
				'size' => intval($item->size),
			]);
	}

	/**
	 * Return the amount of data stored on the server (optionally for an album).
	 *
	 * @param string $album_id
	 *
	 * @return Collection<int,array{type:SizeVariantType,size:int}>
	 */
	public function getSpacePerSizeVariantTypePerAlbum(string $album_id): Collection
	{
		$query = DB::table('albums')
			->where('albums.id', '=', $album_id)
			->joinSub(
				query: DB::table('albums', 'descendants')->select('descendants.id', 'descendants._lft', 'descendants._rgt'),
				as: 'descendants',
				first: function (JoinClause $join) {
					$join->on('albums._lft', '<=', 'descendants._lft')
						->on('albums._rgt', '>=', 'descendants._rgt');
				}
			)
			->joinSub(
				query: DB::table('photos'),
				as: 'photos',
				first: 'photos.album_id',
				operator: '=',
				second: 'descendants.id',
			)
			->joinSub(
				query: DB::table('size_variants')
					->select(['size_variants.id', 'size_variants.photo_id', 'size_variants.type', 'size_variants.filesize'])
					->where('size_variants.type', '!=', 7),
				as: 'size_variants',
				first: 'size_variants.photo_id',
				operator: '=',
				second: 'photos.id',
			)
			->select(
				'size_variants.type',
				DB::raw('SUM(size_variants.filesize) as size')
			)
			->groupBy('size_variants.type')
			->orderBy('size_variants.type', 'asc');

		return $query->get()
			->map(fn ($item) => [
				'type' => SizeVariantType::from($item->type),
				'size' => intval($item->size),
			]);
	}

	/**
	 * Return size statistics per album.
	 *
	 * @param string|null $album_id
	 * @param int|null    $owner_id
	 *
	 * @return Collection<int,array{id:string,left:int,right:int,size:int}>
	 */
	public function getSpacePerAlbum(?string $album_id = null, ?int $owner_id = null)
	{
		$query = DB::table('albums')
			->when($album_id !== null,
				fn ($query) => $query
					->joinSub(
						query: DB::table('albums', 'parent')->select('parent.id', 'parent._lft', 'parent._rgt'),
						as: 'parent',
						first: function (JoinClause $join) {
							$join->on('albums._lft', '>=', 'parent._lft')
								->on('albums._rgt', '<=', 'parent._rgt');
						}
					)
					->where('parent.id', '=', $album_id)
			)
			->when($owner_id !== null, fn ($query) => $query->joinSub(
				query: DB::table('base_albums')->select(['base_albums.id', 'base_albums.owner_id']),
				as: 'base_albums',
				first: 'base_albums.id',
				operator: '=',
				second: 'albums.id'
			)
				->where('base_albums.owner_id', '=', $owner_id))
			->joinSub(
				query: DB::table('photos'),
				as: 'photos',
				first: 'photos.album_id',
				operator: '=',
				second: 'albums.id'
			)
			->joinSub(
				query: DB::table('size_variants')
					->select(['size_variants.id', 'size_variants.photo_id', 'size_variants.filesize'])
					->where('size_variants.type', '!=', 7),
				as: 'size_variants',
				first: 'size_variants.photo_id',
				operator: '=',
				second: 'photos.id'
			)
			->select(
				'albums.id',
				'albums._lft',
				'albums._rgt',
				DB::raw('SUM(size_variants.filesize) as size'),
			)->groupBy('albums.id')
			->orderBy('albums._lft', 'asc');

		return $query
			->get()
			->map(fn ($item) => [
				'id' => strval($item->id),
				'left' => intval($item->_lft),
				'right' => intval($item->_rgt),
				'size' => intval($item->size),
			]);
	}

	/**
	 * Same as above but with full size (including sub-albums).
	 *
	 * @param string|null $album_id
	 * @param int|null    $owner_id
	 *
	 * @return Collection<int,array{id:string,left:int,right:int,size:int}>
	 */
	public function getTotalSpacePerAlbum(?string $album_id = null, ?int $owner_id = null)
	{
		$query = DB::table('albums')
			->when($album_id !== null, fn ($query) => $query->where('albums.id', '=', $album_id))
			->when($owner_id !== null, fn ($query) => $query->joinSub(
				query: DB::table('base_albums')->select(['base_albums.id', 'base_albums.owner_id']),
				as: 'base_albums',
				first: 'base_albums.id',
				operator: '=',
				second: 'albums.id'
			)
				->where('base_albums.owner_id', '=', $owner_id))
			->joinSub(
				query: DB::table('albums', 'descendants')->select('descendants.id', 'descendants._lft', 'descendants._rgt'),
				as: 'descendants',
				first: function (JoinClause $join) {
					$join->on('albums._lft', '<=', 'descendants._lft')
						->on('albums._rgt', '>=', 'descendants._rgt');
				}
			)
			->joinSub(
				query: DB::table('photos'),
				as: 'photos',
				first: 'photos.album_id',
				operator: '=',
				second: 'descendants.id'
			)
			->joinSub(
				query: DB::table('size_variants')
					->select(['size_variants.id', 'size_variants.photo_id', 'size_variants.filesize'])
					->where('size_variants.type', '!=', 7),
				as: 'size_variants',
				first: 'size_variants.photo_id',
				operator: '=',
				second: 'photos.id'
			)
			->select(
				'albums.id',
				'albums._lft',
				'albums._rgt',
				DB::raw('SUM(size_variants.filesize) as size'),
			)->groupBy('albums.id')
			->orderBy('albums._lft', 'asc');

		return $query
			->get()
			->map(fn ($item) => [
				'id' => strval($item->id),
				'left' => intval($item->_lft),
				'right' => intval($item->_rgt),
				'size' => intval($item->size),
			]);
	}

	/**
	 * Return size statistics (number of photos rather than bytes) per album.
	 *
	 * @param string|null $album_id
	 * @param int|null    $owner_id
	 *
	 * @return Collection<int,array{id:string,username:string,title:string,is_nsfw:bool,left:int,right:int,num_photos:int,num_descendants:int}>
	 */
	public function getPhotoCountPerAlbum(?string $album_id = null, ?int $owner_id = null)
	{
		$query = DB::table('albums')
			->when($album_id !== null,
				fn ($query) => $query
					->joinSub(
						query: DB::table('albums', 'parent')->select('parent.id', 'parent._lft', 'parent._rgt'),
						as: 'parent',
						first: function (JoinClause $join) {
							$join->on('albums._lft', '>=', 'parent._lft')
								->on('albums._rgt', '<=', 'parent._rgt');
						}
					)
					->where('parent.id', '=', $album_id)
			)
			->joinSub(
				query: DB::table('base_albums')->select(['base_albums.id', 'base_albums.owner_id', 'base_albums.title', 'base_albums.is_nsfw']),
				as: 'base_albums',
				first: 'base_albums.id',
				operator: '=',
				second: 'albums.id'
			)
			->when($owner_id !== null, fn ($query) => $query->where('base_albums.owner_id', '=', $owner_id))
			->joinSub(
				query: DB::table('photos')->select(['photos.id', 'photos.album_id']),
				as: 'photos',
				first: 'photos.album_id',
				operator: '=',
				second: 'albums.id'
			)
			->joinSub(
				query: DB::table('users')->select(['users.id', 'users.username']),
				as: 'users',
				first: 'users.id',
				operator: '=',
				second: 'base_albums.owner_id'
			)
			->select(
				'albums.id',
				'username',
				'base_albums.title',
				'base_albums.is_nsfw',
				'albums._lft',
				'albums._rgt',
				DB::raw('COUNT(photos.id) as num_photos'),
			)->groupBy(
				'albums.id',
				'username',
				'base_albums.title',
				'base_albums.is_nsfw',
				'albums._lft',
				'albums._rgt',
			)
			->orderBy('albums._lft', 'asc');

		return $query
			->get()
			->map(fn ($item) => [
				'id' => strval($item->id),
				'username' => strval($item->username),
				'title' => strval($item->title),
				'is_nsfw' => boolval($item->is_nsfw),
				'left' => intval($item->_lft),
				'right' => intval($item->_rgt),
				'num_photos' => intval($item->num_photos),
				'num_descendants' => intval(($item->_rgt - $item->_lft - 1) / 2),
			]);
	}

	/**
	 * Same as above but including sub-albums.
	 *
	 * @param string|null $album_id
	 * @param int|null    $owner_id
	 *
	 * @return Collection<int,array{id:string,username:string,title:string,is_nsfw:bool,left:int,right:int,num_photos:int,num_descendants:int}>
	 */
	public function getTotalPhotoCountPerAlbum(?string $album_id = null, ?int $owner_id = null)
	{
		$query = DB::table('albums')
			->when($album_id !== null, fn ($query) => $query->where('albums.id', '=', $album_id))
			->joinSub(
				query: DB::table('base_albums')->select(['base_albums.id', 'base_albums.owner_id', 'base_albums.title', 'base_albums.is_nsfw']),
				as: 'base_albums',
				first: 'base_albums.id',
				operator: '=',
				second: 'albums.id'
			)
			->when($owner_id !== null, fn ($query) => $query->where('base_albums.owner_id', '=', $owner_id))
			->joinSub(
				query: DB::table('albums', 'descendants')->select('descendants.id', 'descendants._lft', 'descendants._rgt'),
				as: 'descendants',
				first: function (JoinClause $join) {
					$join->on('albums._lft', '<=', 'descendants._lft')
						->on('albums._rgt', '>=', 'descendants._rgt');
				}
			)
			->joinSub(
				query: DB::table('photos')->select(['photos.id', 'photos.album_id']),
				as: 'photos',
				first: 'photos.album_id',
				operator: '=',
				second: 'descendants.id'
			)
			->joinSub(
				query: DB::table('users')->select(['users.id', 'users.username']),
				as: 'users',
				first: 'users.id',
				operator: '=',
				second: 'base_albums.owner_id'
			)
			->select(
				'albums.id',
				'username',
				'base_albums.title',
				'base_albums.is_nsfw',
				'albums._lft',
				'albums._rgt',
				DB::raw('COUNT(photos.id) as num_photos'),
			)->groupBy(
				'albums.id',
				'username',
				'base_albums.title',
				'base_albums.is_nsfw',
				'albums._lft',
				'albums._rgt',
			)
			->orderBy('albums._lft', 'asc');

		return $query
			->get()
			->map(fn ($item) => [
				'id' => strval($item->id),
				'username' => strval($item->username),
				'title' => strval($item->title),
				'is_nsfw' => boolval($item->is_nsfw),
				'left' => intval($item->_lft),
				'right' => intval($item->_rgt),
				'num_photos' => intval($item->num_photos),
				'num_descendants' => intval(($item->_rgt - $item->_lft - 1) / 2),
			]);
	}
}
