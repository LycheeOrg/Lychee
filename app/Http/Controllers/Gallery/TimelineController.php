<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Actions\Photo\Timeline;
use App\Http\Requests\Timeline\DatedTimelineRequest;
use App\Http\Requests\Timeline\GetTimelineRequest;
use App\Http\Resources\Timeline\InitResource;
use App\Http\Resources\Timeline\TimelineResource;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Controller;
use Spatie\LaravelData\Data;

/**
 * Controller responsible for the Timeline data.
 */
class TimelineController extends Controller
{
	public function __invoke(DatedTimelineRequest $request, Timeline $timeline): Data
	{
		$pagination_limit = Configs::getValueAsInt('timeline_photos_pagination_limit');

		if ($request->date !== null) {
			$youngers = $timeline->countYounger($request->date);
			Paginator::currentPageResolver(fn () => max(0, floor($youngers / $pagination_limit) - 1));
		}

		/** @var LengthAwarePaginator<Photo> $photoResults */
		/** @disregard P1013 Undefined method withQueryString() (stupid intelephense) */
		$photoResults = $timeline->do()->paginate($pagination_limit);

		return TimelineResource::fromData($photoResults);
	}

	/**
	 * Return init Search.
	 *
	 * @return InitResource
	 */
	public function init(): Data
	{
		return new InitResource();
	}

	/**
	 * Return all the dates of the timeline.
	 *
	 * @param GetTimelineRequest $request
	 * @param Timeline           $timeline
	 *
	 * @return array
	 */
	public function dates(GetTimelineRequest $request, Timeline $timeline): array
	{
		return $timeline->dates();
	}
}