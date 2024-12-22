<?php

namespace App\Http\Controllers\Gallery;

use App\Actions\Photo\Timeline;
use App\Http\Requests\Photo\GetTimelineRequest;
use App\Http\Resources\Timeline\InitResource;
use App\Http\Resources\Timeline\TimelineResource;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Spatie\LaravelData\Data;

/**
 * Controller responsible for the Timeline data.
 */
class TimelineController extends Controller
{
	public function __invoke(GetTimelineRequest $request, Timeline $timeline): Data
	{
		/** @var LengthAwarePaginator<Photo> $photoResults */
		/** @disregard P1013 Undefined method withQueryString() (stupid intelephense) */
		$photoResults = $timeline->do()->paginate(Configs::getValueAsInt('timeline_photos_pagination_limit'));

		return TimelineResource::fromData($photoResults);
	}

	/**
	 * Return init Search.
	 *
	 * @param GetTimelineRequest $request
	 *
	 * @return InitResource
	 */
	public function init(GetTimelineRequest $request): Data
	{
		return new InitResource();
	}
}