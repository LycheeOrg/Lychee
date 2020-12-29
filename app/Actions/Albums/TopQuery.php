<?php

namespace App\Actions\Albums;

use AccessControl;
use App\Actions\Albums\Extensions\PublicIds;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait TopQuery
{
	use PublicIds;

	private function createTopleveAlbumsQuery(): Builder
	{
		if (AccessControl::is_admin()) {
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

			return Album::with(['owner'])
				->whereIsRoot()
				->orderBy('owner_id', 'ASC');
		}

		if (AccessControl::is_logged_in()) {
			$id = AccessControl::id();

			return Album::with(['owner'])
				->whereIsRoot()
				->where(function ($query) use ($id) {
					$query = $query->where('owner_id', '=', $id)
						->orWhereIn(
							'id',
							DB::table('user_album')->select('album_id')->where('user_id', '=', $id)
						)
						->orWhere(function ($_query) {
							$_query->where('public', '=', true)->where('viewable', '=', true);
						});
				})
				->orderBy('owner_id', 'ASC');
		}

		return Album::whereIsRoot()
			->where('public', '=', '1')
			->where('viewable', '=', '1');
	}
}
