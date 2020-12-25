<?php

namespace App\Actions\Albums;

use AccessControl;
use App\Models\Album;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait TopQuery
{
	private function createTopleveAlbumsQuery(): Builder
	{
		if (AccessControl::is_logged_in()) {
			$sql = Album::with([
				'owner',
			])->where('parent_id', '=', null);

			$id = AccessControl::id();

			if ($id > 0) {
				$sql = $sql->where(function ($query) use ($id) {
					$query = $query->where('owner_id', '=', $id);
					$query = $query->orWhereIn('id', DB::table('user_album')->select('album_id')
						->where('user_id', '=', $id));
					$query = $query->orWhere(function ($_query) {
						$_query->where('public', '=', true)->where('viewable', '=', true);
					});
				});
			}

			return $sql->orderBy('owner_id', 'ASC');
		}

		return Album::where('public', '=', '1')
			->where('viewable', '=', '1')
			->where('parent_id', '=', null);
	}
}
