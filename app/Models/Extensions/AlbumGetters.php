<?php

namespace App\Models\Extensions;

use App\Actions\Albums\Extensions\PublicIds;
use App\Actions\Albums\Extensions\PublicViewable;
use App\Models\Configs;
use App\Models\Photo;

trait AlbumGetters
{
	use CustomSort;
	use PublicViewable;
	use AlbumQuery;

	/**
	 * given an Album return the sorting column & order for the pictures or the default ones.
	 *
	 * @param Album
	 *
	 * @return array
	 */
	public function get_sort(): array
	{
		if ($this->sorting_col == null || $this->sorting_col == '') {
			$sort_col = Configs::get_value('sorting_Photos_col');
			$sort_order = Configs::get_value('sorting_Photos_order');
		} else {
			$sort_col = $this->sorting_col;
			$sort_order = $this->sorting_order;
		}

		return [$sort_col, $sort_order];
	}

	/**
	 * Return the Album license or the default one.
	 *
	 * @return string
	 */
	public function get_license(): string
	{
		if ($this->license == 'none') {
			return Configs::get_value('default_license');
		}

		return $this->license;
	}

	/**
	 * Return the list of photos.
	 */
	public function get_photos()
	{
		return $this->photos();
	}

	/**
	 * Return a Query with all the subsequent pictures.
	 *
	 * @return Builder
	 */
	public function get_all_photos()
	{
		return Photo::leftJoin('albums', 'photos.album_id', '=', 'albums.id')
			->select('photos.*')
			->where('albums._lft', '>=', $this->_lft)
			->where('albums._rgt', '<=', $this->_rgt);
	}

	public function get_thumbs()
	{
		// SQL REQUIRES REVIEW
		$sql = Photo::leftJoin('albums', 'photos.album_id', '=', 'albums.id')
		   ->select('photos.*');

		//? apply safety filter : Do not leak pictures which are not ours
		$forbiddenID = resolve(PublicIds::class)->getNotAccessible();
		if ($forbiddenID != null && !$forbiddenID->isEmpty()) {
			$sql = $sql->whereNotIn('album_id', $forbiddenID);
		}

//		$fp = fopen('/tmp/vardump.txt', 'a');
//		fwrite($fp, serialize($this->description) . "\n");
//		fclose($fp);
		// FINAL
//		if ($this->cover && $this->cover > 0) {
//			// cover set return it
//			return $sql->where('photos.id', '=', $this->cover)
//				->get()
//				->map(fn ($photo) => $photo->toThumb());
//		}
		// TEST
		if ($this->description && $this->description != '') {
			return $sql->where('photos.id', '=', $this->description)
				->get()
				->map(fn ($photo) => $photo->toThumb());
		}
		// default: pick random starred photo as cover (one is enough)
		[$sort_col, $sort_order] = $this->get_sort();

		$sql = $this->get_all_photos();
		if ($forbiddenID != null && !$forbiddenID->isEmpty()) {
			$sql = $sql->whereNotIn('album_id', $forbiddenID);
		}

		return $sql->orderBy('star', 'DESC')
			->orderBy($sort_col, $sort_order)
			->orderBy('photos.id', 'ASC')
			->limit(1)
			->get()
			->map(fn ($photo) => $photo->toThumb());
	}

	public function get_children()
	{
		$sortingCol = Configs::get_value('sorting_Albums_col');
		$sortingOrder = Configs::get_value('sorting_Albums_order');

		$sql = self::initQuery()->where('parent_id', '=', $this->id);
		//? apply safety filter : Do not leak albums which are not visible
		$sql = $this->publicViewable($sql);

		return $this->customSort($sql, $sortingCol, $sortingOrder);
	}
}
