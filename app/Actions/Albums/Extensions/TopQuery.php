<?php

namespace App\Actions\Albums\Extensions;

use App\Facades\AccessControl;
use App\Models\Album;
use Illuminate\Database\Eloquent\Builder;

trait TopQuery
{
	private function createTopleveAlbumsQuery(): Builder
	{
		$baseQuery = Album::query()->whereIsRoot();
		$query = resolve(PublicIds::class)->publicViewable($baseQuery);
		if (AccessControl::is_logged_in()) {
			return $query->orderBy('owner_id', 'ASC');
		}

		return $query;
	}
}
