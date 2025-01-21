<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

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
		$pure_duplicates = $duplicateFinder->checkCount(must_be_within_same_album: false, must_have_same_checksum: true, must_have_same_title: false);
		$title_duplicates = $duplicateFinder->checkCount(must_be_within_same_album: true, must_have_same_checksum: false, must_have_same_title: true);
		$duplicates_with_album = $duplicateFinder->checkCount(must_be_within_same_album: true, must_have_same_checksum: true, must_have_same_title: false);

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
		return $duplicateFinder->search(
			must_be_within_same_album: $request->with_album_constraint, // false,
			must_have_same_checksum: $request->with_checksum_constraint, // true,
			must_have_same_title: $request->with_title_constraint, // false
		)->map(fn (object $model) => Duplicate::fromModel($model));
	}
}
