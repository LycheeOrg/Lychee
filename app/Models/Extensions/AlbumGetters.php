<?php

namespace App\Models\Extensions;

use App\Actions\Albums\Extensions\PublicIds;
use App\Models\Configs;
use App\Models\Photo;

trait AlbumGetters
{
	use CustomSort;

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
		[$sort_col, $sort_order] = $this->get_sort();

		$sql = $this->get_all_photos();

		//? apply safety filter : Do not leak pictures which are not ours
		$publicAlbumsId = resolve(PublicIds::class)->getPublicAlbumsId();
		if ($publicAlbumsId != null && !$publicAlbumsId->isEmpty()) {
			$sql = $sql->whereIn('album_id', $publicAlbumsId);
		}

		return $sql->orderBy('star', 'DESC')
			->orderBy($sort_col, $sort_order)
			->orderBy('photos.id', 'ASC')
			->limit(3)
			->get()
			->map(fn ($photo) => $photo->toThumb());
	}

	public function get_children()
	{
		$sortingCol = Configs::get_value('sorting_Albums_col');
		$sortingOrder = Configs::get_value('sorting_Albums_order');

		return $this->customSort($this->children(), $sortingCol, $sortingOrder);
	}
}
