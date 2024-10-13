<?php

namespace App\Actions\Statistics;

use App\Enum\SizeVariantType;
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
			->leftJoin('photos', 'photos.owner_id', '=', 'users.id')
			->leftJoin('size_variants', 'size_variants.photo_id', '=', 'photos.id')
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
	public function getSpacePerSizeVariantType(?int $owner_id = null): Collection
	{
		return DB::table('size_variants')
			->join('photos', 'photos.id', '=', 'size_variants.photo_id')
			->when($owner_id !== null, fn ($query) => $query->where('photos.owner_id', '=', $owner_id))
			->select(
				'size_variants.type',
				DB::raw('SUM(size_variants.filesize) as size')
			)
			->groupBy('size_variants.type')
			->orderBy('size_variants.type', 'asc')
			->get()
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
			->when($album_id !== null, fn ($query) => $query->where('albums.id', '=', $album_id))
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->when($owner_id !== null, fn ($query) => $query->where('base_albums.owner_id', '=', $owner_id))
			->join('photos', 'photos.album_id', '=', 'albums.id')
			->join('size_variants', 'size_variants.photo_id', '=', 'photos.id')
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
	 * Same as above but with full size.
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
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->when($owner_id !== null, fn ($query) => $query->where('base_albums.owner_id', '=', $owner_id))
			->join('albums as descendants',
				fn ($q) => $q->on('albums._lft', '<=', 'descendants._lft')
				->on('albums._rgt', '>=', 'descendants._rgt'))
			->join('photos', 'photos.album_id', '=', 'descendants.id')
			->join('size_variants', 'size_variants.photo_id', '=', 'photos.id')
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
	 * Return size statistics per album.
	 *
	 * @param string|null $album_id
	 * @param int|null    $owner_id
	 *
	 * @return Collection<int,array{id:string,username:string,title:string,is_nsfw:bool,left:int,right:int,num_photos:int,num_descendants:int}>
	 */
	public function getPhotoCountPerAlbum(?string $album_id = null, ?int $owner_id = null)
	{
		$query = DB::table('albums')
			->when($album_id !== null, fn ($query) => $query->where('albums.id', '=', $album_id))
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->when($owner_id !== null, fn ($query) => $query->where('base_albums.owner_id', '=', $owner_id))
			->join('photos', 'photos.album_id', '=', 'albums.id')
			->join('users', 'users.id', '=', 'base_albums.owner_id')
			->select(
				'albums.id',
				'username',
				'base_albums.title',
				'is_nsfw',
				'albums._lft',
				'albums._rgt',
				DB::raw('COUNT(photos.id) as num_photos'),
			)->groupBy(
				'albums.id',
				'username',
				'base_albums.title',
				'is_nsfw',
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
	 * Same as above but with full size.
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
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->when($owner_id !== null, fn ($query) => $query->where('base_albums.owner_id', '=', $owner_id))
			->join('albums as descendants',
				fn ($q) => $q->on('albums._lft', '<=', 'descendants._lft')
				->on('albums._rgt', '>=', 'descendants._rgt'))
			->join('photos', 'photos.album_id', '=', 'descendants.id')
			->join('users', 'users.id', '=', 'base_albums.owner_id')
			->select(
				'albums.id',
				'username',
				'base_albums.title',
				'is_nsfw',
				'albums._lft',
				'albums._rgt',
				DB::raw('COUNT(photos.id) as num_photos'),
			)->groupBy(
				'albums.id',
				'username',
				'base_albums.title',
				'is_nsfw',
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