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
			$baseQuery = Album::query()->whereIsRoot();

			return $this->publicViewable($baseQuery)->orderBy('owner_id', 'ASC');
		}

		return $this->publicViewable(Album::query()->whereIsRoot());
	}
}
