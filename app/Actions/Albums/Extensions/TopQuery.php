<?php

namespace App\Actions\Albums\Extensions;

use AccessControl;
use App\Models\Album;
use Illuminate\Database\Eloquent\Builder;

trait TopQuery
{
	use PublicViewable;

	private function createTopleveAlbumsQuery(): Builder
	{
		if (AccessControl::is_logged_in()) {
			$baseQuery = Album::with(['owner'])->whereIsRoot();

			return $this->publicViewable($baseQuery)->orderBy('owner_id', 'ASC');
		}

		return $this->publicViewable(Album::whereIsRoot());

		// $id_not_accessible = $this->getNotAccessible();

		// $ret = Album::whereIsRoot()
		// 	->addSelect([
		// 		'photo_1' =>
		// 		Photo::leftJoin('albums as s', 'album_id', '=', 's.id')
		// 			->select('photos.id')
		// 			->where('_lft', '<=', DB::raw('s._lft'))
		// 			->where('s._rgt', '<=', DB::raw('_rgt'))
		// 			->whereNotIn('s.id', $id_not_accessible)
		// 			->orderBy('takestamp', 'desc')
		// 			->orderBy('photos.created_at', 'desc')
		// 			->limit(1),
		// 		'photo_2' =>
		// 		Photo::leftJoin('albums as s', 'album_id', '=', 's.id')
		// 			->select('photos.id')
		// 			->where('_lft', '<=', DB::raw('s._lft'))
		// 			->where('s._rgt', '<=', DB::raw('_rgt'))
		// 			->whereNotIn('s.id', $id_not_accessible)
		// 			->orderBy('takestamp', 'desc')
		// 			->orderBy('photos.created_at', 'desc')
		// 			->skip(1)
		// 			->limit(1),
		// 		'photo_3' =>
		// 		Photo::leftJoin('albums as s', 'album_id', '=', 's.id')
		// 			->select('photos.id')
		// 			->where('_lft', '<=', DB::raw('s._lft'))
		// 			->where('s._rgt', '<=', DB::raw('_rgt'))
		// 			->whereNotIn('s.id', $id_not_accessible)
		// 			->orderBy('takestamp', 'desc')
		// 			->orderBy('photos.created_at', 'desc')
		// 			->skip(2)
		// 			->limit(1)
		// 	])
		// 	->get();
		// dd($ret);
	}
}
