<?php

namespace App\Http\Controllers\Admin\Maintenance;

use App\Http\Controllers\Admin\Maintenance\Model\Album;
use App\Http\Requests\Maintenance\FullTreeUpdateRequest;
use App\Http\Requests\Maintenance\MaintenanceRequest;
use App\Http\Resources\Diagnostics\AlbumTree;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Maybe the album tree is broken.
 * We fix it here.
 */
class FullTree extends Controller
{
	/**
	 * Clean the path from all files excluding $this->skip.
	 *
	 * @return void
	 */
	public function do(FullTreeUpdateRequest $request): void
	{
		DB::beginTransaction();

		$keyName = 'id';
		$albumInstance = new Album();
		batch()->updateMultipleCondition($albumInstance, $request->albums(), $keyName);

		DB::commit();
	}

	/**
	 * Check whether there are files to be removed.
	 * If not, we will not display the module to reduce complexity.
	 *
	 * @return Collection<int,AlbumTree>
	 */
	public function check(MaintenanceRequest $request): Collection
	{
		$albums = Album::query()->join('base_albums', 'base_albums.id', '=', 'albums.id')->select(['albums.id', 'title', 'parent_id', '_lft', '_rgt'])->orderBy('_lft', 'asc')->toBase()->get();

		return AlbumTree::collect($albums);
	}
}
