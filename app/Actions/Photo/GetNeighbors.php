<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo;

use App\Contracts\Models\AbstractAlbum;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;

class GetNeighbors
{
	/**
	 * Returns the ID of the next and previous photo in the sorted album.
	 *
	 * @param Photo         $photo
	 * @param AbstractAlbum $album
	 *
	 * @return array{previous_photo_id: ?string, next_photo_id: ?string}
	 */
	public function do(Photo $photo, AbstractAlbum $album): array
	{
		$sorting = $album->getEffectivePhotoSorting();
		$column = $sorting->column->toColumn();
		$order = $sorting->order->toOrder();

		$prev = $this->getNeighbor($photo, $album, $column, $order, true);
		$next = $this->getNeighbor($photo, $album, $column, $order, false);

		return [
			'previous_photo_id' => $prev?->id,
			'next_photo_id' => $next?->id,
		];
	}

	/**
	 * @param Photo         $photo
	 * @param AbstractAlbum $album
	 * @param string        $column
	 * @param string        $order
	 * @param bool          $is_previous
	 *
	 * @return Photo|null
	 */
	private function getNeighbor(Photo $photo, AbstractAlbum $album, string $column, string $order, bool $is_previous): ?Photo
	{
		$query = $album->photos()->getQuery();

		$current_value = $photo->{$column};
		$current_id = $photo->id;

		// If we want the previous photo:
		// In ASC order: value < current_value OR (value == current_value AND id < current_id)
		// In DESC order: value > current_value OR (value == current_value AND id > current_id)

		// If we want the next photo:
		// In ASC order: value > current_value OR (value == current_value AND id > current_id)
		// In DESC order: value < current_value OR (value == current_value AND id < current_id)

		$is_asc = ($order === 'asc');
		$look_for_less = ($is_previous && $is_asc) || (!$is_previous && !$is_asc);

		$query->where(function (Builder $q) use ($column, $current_value, $current_id, $look_for_less) {
			$op = $look_for_less ? '<' : '>';
			$q->where($column, $op, $current_value)
			  ->orWhere(function (Builder $q2) use ($column, $current_value, $current_id, $op) {
			  	$q2->where($column, '=', $current_value)
					 ->where('id', $op, $current_id);
			  });
		});

		// Order the results to get the closest one
		$sort_order = $look_for_less ? 'desc' : 'asc';
		$query->orderBy($column, $sort_order);
		$query->orderBy('id', $sort_order);

		return $query->first();
	}
}
