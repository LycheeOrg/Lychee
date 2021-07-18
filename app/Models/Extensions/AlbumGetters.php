<?php

namespace App\Models\Extensions;

use App\Actions\Albums\Extensions\PublicIds;
use App\Actions\Albums\Extensions\PublicViewable;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait AlbumGetters
{
	use CustomSort;
	use PublicViewable;

	/**
	 * given an Album return the sorting column & order for the pictures or the default ones.
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
	 * Return a query builder or an SQL relation for the list of photos.
	 *
	 * See comment in {@link \App\SmartAlbums\BareSmartAlbum} why we need
	 * an ambitious return type here.
	 *
	 * @return Builder|HasMany
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
	public function get_all_photos(): Builder
	{
		return Photo::query()
			->leftJoin('albums', 'photos.album_id', '=', 'albums.id')
			->select('photos.*')
			->where('albums._lft', '>=', $this->_lft)
			->where('albums._rgt', '<=', $this->_rgt);
	}

	public function get_thumb(): ?Thumb
	{
		if ($this->cover != null) {
			$cover = $this->cover;
		} else {
			[$sort_col, $sort_order] = $this->get_sort();

			/* @var Builder|HasMany $sql */
			if ($this->is_smart()) {
				$sql = $this->get_photos();
			} else {
				$sql = $this->get_all_photos();
			}
			$sql->with('size_variants_raw');

			//? apply safety filter : Do not leak pictures which are not ours
			$forbiddenID = resolve(PublicIds::class)->getNotAccessible();

			if ($forbiddenID != null && !$forbiddenID->isEmpty()) {
				$sql = $sql->where(
					fn ($q) => $q->whereNull('album_id')
						->orWhereNotIn('album_id', $forbiddenID)
				);
			}

			$cover = $sql->orderBy('star', 'DESC')
				->orderBy($sort_col, $sort_order)
				->orderBy('photos.id', 'ASC')
				->limit(1)
				->first();
		}

		return Thumb::createFromPhoto($cover);
	}

	public function get_children()
	{
		$sortingCol = Configs::get_value('sorting_Albums_col');
		$sortingOrder = Configs::get_value('sorting_Albums_order');

		$sql = self::query()->where('parent_id', '=', $this->id);
		//? apply safety filter : Do not leak albums which are not visible
		$sql = $this->publicViewable($sql);

		return $this->customSort($sql, $sortingCol, $sortingOrder);
	}
}
