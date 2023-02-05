<?php

namespace App\Models\Extensions;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Trait SharedWithCurrentUserQuery.
 */
trait SharedWithCurrentUserQuery
{
	/**
	 * Generates a query which retrieves the associated user_base_album row.
	 * Note: we leaverage that the base_album.id = {album|tag_album}.id to simplify the query!
	 *
	 * @return Builder
	 */
	protected function sharedWithCurrentUser(string $modelTable): Builder
	{
		return DB::table('user_base_album', 'uba')
			->whereColumn('uba.base_album_id', '=', $modelTable . '.id')
			->where('user_id', '=', Auth::id());
	}
}