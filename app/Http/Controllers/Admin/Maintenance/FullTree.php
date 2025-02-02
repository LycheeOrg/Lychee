<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Events\AlbumRouteCacheUpdated;
use App\Http\Controllers\Admin\Maintenance\Model\Album;
use App\Http\Requests\Maintenance\FullTreeUpdateRequest;
use App\Http\Requests\Maintenance\MaintenanceRequest;
use App\Http\Resources\Diagnostics\AlbumTree;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

/**
 * Maybe the album tree is broken.
 * We fix it here.
 */
class FullTree extends Controller
{
	/**
	 * Apply the fix suggested.
	 * ! This may break the installation. Not our problem.
	 *
	 * @return void
	 */
	public function do(FullTreeUpdateRequest $request): void
	{
		$keyName = 'id';
		$albumInstance = new Album();
		batch()->update($albumInstance, $request->albums(), $keyName);

		AlbumRouteCacheUpdated::dispatch();
	}

	/**
	 * Display the current full tree of albums.
	 *
	 * @return Collection<int,AlbumTree>
	 */
	public function check(MaintenanceRequest $request): Collection
	{
		$albums = Album::query()->join('base_albums', 'base_albums.id', '=', 'albums.id')->select(['albums.id', 'title', 'parent_id', '_lft', '_rgt'])->orderBy('_lft', 'asc')->toBase()->get();

		return AlbumTree::collect($albums);
	}
}
