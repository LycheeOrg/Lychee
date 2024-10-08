<?php

namespace App\Actions\Statistics;

use App\Enum\SizeVariantType;
use App\Facades\Helpers;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * We compute the size usage from the size variants.
 * Do note that this number may be slightly off due to the way we store pictures in the database:
 * row are duplicates for pictures, but the file is stored only once.
 */
class GetSizes
{
	/**
	 * Return the amount of data stored on the server.
	 *
	 * @return Collection<int,array{type:SizeVariantType,size:int,formatted:string}>
	 */
	public function getFullSize(): Collection
	{
		return DB::table('size_variants')
			->select(
				'type',
				DB::raw('SUM(filesize) as size')
			)
			->groupBy('type')
			->get()
			->map(fn ($item) => [
				'type' => SizeVariantType::from($item->type),
				'size' => intval($item->size),
				'formatted' => Helpers::getSymbolByQuantity((float) $item->size),
			]);
	}

	/**
	 * Return size statistics per album.
	 *
	 * @param string|null $albumId
	 *
	 * @return Collection<int,array{title:string,left:int,right:int,num_photos:int,num_descendants:int,size:int,formatted:string}>
	 */
	public function getAlbumsSizes(?string $albumId = null)
	{
		$query = DB::table('albums')
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->join('photos', 'photos.album_id', '=', 'albums.id')
			->join('size_variants', 'size_variants.photo_id', '=', 'photos.id')
			->select(
				'albums.id',
				'base_albums.title',
				'albums._lft',
				'albums._rgt',
				DB::raw('COUNT(photos.id) as num_photos'),
				DB::raw('SUM(size_variants.filesize) as size'),
			)->when($albumId !== null, fn ($query) => $query->where('albums.id', '=', $albumId))
			->groupBy('albums.id')
			->orderBy('albums._lft', 'asc');

		return $query
			->get()
			->map(fn ($item) => [
				'title' => strval($item->title),
				'left' => intval($item->_lft),
				'right' => intval($item->_rgt),
				'num_photos' => intval($item->num_photos),
				'num_descendants' => intval(($item->_rgt - $item->_lft - 1) / 2),
				'size' => intval($item->size),
				'formatted' => Helpers::getSymbolByQuantity((float) $item->size),
			]);
	}

	/**
	 * Same as above but with full size.
	 *
	 * @param string|null $albumId
	 *
	 * @return Collection<int,array{title:string,left:int,right:int,num_photos:int,num_descendants:int,size:int,formatted:string}>
	 */
	public function getTotalAlbumsSizes(?string $albumId = null)
	{
		$query = DB::table('albums')
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->join('albums as descendants',
				fn ($q) => $q->on('albums._lft', '<=', 'descendants._lft')
				->on('albums._rgt', '>=', 'descendants._rgt'))
			->join('photos', 'photos.album_id', '=', 'descendants.id')
			->join('size_variants', 'size_variants.photo_id', '=', 'photos.id')
			->select(
				'albums.id',
				'base_albums.title',
				'albums._lft',
				'albums._rgt',
				DB::raw('COUNT(photos.id) as num_photos'),
				DB::raw('SUM(size_variants.filesize) as size'),
			)->when($albumId !== null, fn ($query) => $query->where('albums.id', '=', $albumId))
			->groupBy('albums.id')
			->orderBy('albums._lft', 'asc');
		dd($query->toSql());

		return $query
			->get()
			->map(fn ($item) => [
				'title' => strval($item->title),
				'left' => intval($item->_lft),
				'right' => intval($item->_rgt),
				'num_photos' => intval($item->num_photos),
				'num_descendants' => intval(($item->_rgt - $item->_lft - 1) / 2),
				'size' => intval($item->size),
				'formatted' => Helpers::getSymbolByQuantity((float) $item->size),
			]);
	}
}