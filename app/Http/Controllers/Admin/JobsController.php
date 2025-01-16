<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Jobs\ShowJobsRequest;
use App\Http\Resources\Models\JobHistoryResource;
use App\Models\Configs;
use App\Models\JobHistory;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelData\PaginatedDataCollection;

class JobsController extends Controller
{
	/**
	 * List jobs executed on the server and the pending ones.
	 *
	 * @param ShowJobsRequest $request
	 *
	 * @return PaginatedDataCollection<(int|string),JobHistoryResource>
	 */
	public function list(ShowJobsRequest $request): PaginatedDataCollection
	{
		$jobs = JobHistory::with(['owner'])
			->when(!Auth::user()->may_administrate, fn ($query) => $query->where('owner_id', '=', Auth::id()))
			->orderBy('id', 'desc')
			->paginate(Configs::getValueAsInt('log_max_num_line'));

		return JobHistoryResource::collect($jobs, PaginatedDataCollection::class);
	}
}
