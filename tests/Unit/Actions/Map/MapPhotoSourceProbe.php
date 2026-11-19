<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Unit\Actions\Map;

use App\Actions\Map\ResolvesMapPhotoSource;
use App\Contracts\Models\AbstractAlbum;
use App\Eloquent\FixedQueryBuilder;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Exposes {@see ResolvesMapPhotoSource}'s private methods for testing.
 */
class MapPhotoSourceProbe
{
	use ResolvesMapPhotoSource;

	/**
	 * @return FixedQueryBuilder<Photo>
	 */
	public function rootQuery(?User $user): FixedQueryBuilder
	{
		return $this->resolveRootQuery($user);
	}

	/**
	 * @return Relation<Photo,AbstractAlbum&\Illuminate\Database\Eloquent\Model,mixed>|Builder<Photo>
	 */
	public function albumQuery(AbstractAlbum $album, bool $include_sub_albums): Relation|Builder
	{
		return $this->resolveAlbumQuery($album, $include_sub_albums);
	}
}
