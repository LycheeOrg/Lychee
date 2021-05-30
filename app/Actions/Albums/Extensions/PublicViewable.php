<?php

namespace App\Actions\Albums\Extensions;

use App\Facades\AccessControl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait PublicViewable
{
	/**
	 * Simple function to filter public viewable albums.
	 */
	public function publicViewable(Builder $query): Builder
	{
		if (AccessControl::is_admin()) {
			return $query;
		}

		if (AccessControl::is_logged_in()) {
			$id = AccessControl::id();

			return $query->where(fn ($query) => $query->where('owner_id', '=', $id)
				->orWhereIn('id', DB::table('user_album')->select('album_id')->where('user_id', '=', $id))
				->orWhere(fn ($q) => $q->where('public', '=', '1')->where('viewable', '=', '1')));
		}

		// or PUBLIC AND VIEWABLE (not hidden)
		return $query->where('public', '=', '1')->where('viewable', '=', '1');
	}
}
