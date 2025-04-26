<?php

/**
 * SPDX-License-Identifier: MIT
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
	 */
	public function do(FullTreeUpdateRequest $request): void
	{
		$key_name = 'id';
		$album_instance = new Album();
		batch()->update($album_instance, $request->albums(), $key_name);

		AlbumRouteCacheUpdated::dispatch();
	}

	/**
	 * Display the current full tree of albums.
	 *
	 * @return Collection<int,AlbumTree>
	 */
	public function check(MaintenanceRequest $request): Collection
	{
		// phpstan does not accept the staticMethod.dynamicCall ignore hint that's why the whole line is ignored
		// @phpstan-ignore-next-line
		$albums = Album::query()->join('base_albums', 'base_albums.id', '=', 'albums.id')->select(['albums.id', 'title', 'parent_id', '_lft', '_rgt'])->orderBy('_lft', 'asc')->toBase()->get();

		return AlbumTree::collect($albums);
	}
}