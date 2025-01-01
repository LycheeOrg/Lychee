<?php

namespace App\Http\Controllers\Admin\Maintenance;

use App\Actions\Photo\DuplicateFinder as PhotoDuplicateFinder;
use App\Http\Requests\Maintenance\MaintenanceRequest;
use App\Http\Requests\Maintenance\SearchDuplicateRequest;
use App\Http\Resources\Models\Duplicates\Duplicate;
use App\Http\Resources\Models\Duplicates\DuplicateCount;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

/**
 * Maybe the album tree is broken.
 * We fix it here.
 */
class DuplicateFinder extends Controller
{
	/**
	 * Get the number of duplicates.
	 *
	 * @return DuplicateCount
	 */
	public function check(MaintenanceRequest $request, PhotoDuplicateFinder $duplicateFinder): DuplicateCount
	{
		$pure_duplicates = $duplicateFinder->checkCount(with_album_constraint: false, with_checksum_constraint: true, with_title_constraint: false);
		$title_duplicates = null;
		$duplicates_with_album = null;

		if ($request->is_se()) {
			$title_duplicates = $duplicateFinder->checkCount(with_album_constraint: true, with_checksum_constraint: false, with_title_constraint: true);
			$duplicates_with_album = $duplicateFinder->checkCount(with_album_constraint: true, with_checksum_constraint: true, with_title_constraint: false);
		}

		return new DuplicateCount(
			pure_duplicates: $pure_duplicates,
			title_duplicates: $title_duplicates,
			duplicates_within_album: $duplicates_with_album,
		);
	}

	/**
	 * Get the actual list of duplicates instead of just the counts.
	 *
	 * @param SearchDuplicateRequest $request
	 * @param PhotoDuplicateFinder   $duplicateFinder
	 *
	 * @return Collection<int,Duplicate>
	 */
	public function get(SearchDuplicateRequest $request, PhotoDuplicateFinder $duplicateFinder): Collection
	{
		return Duplicate::collect($duplicateFinder->search(
			with_album_constraint: $request->with_album_constraint, // false,
			with_checksum_constraint: $request->with_checksum_constraint, // true,
			with_title_constraint: $request->with_title_constraint, // false
		));
	}
}
