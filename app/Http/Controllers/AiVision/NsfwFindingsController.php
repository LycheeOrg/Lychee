<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\AiVision;

use App\Http\Requests\Nsfw\DeleteNsfwFindingsByPhotosRequest;
use App\Http\Requests\Nsfw\DeleteNsfwFindingsRequest;
use App\Http\Requests\Nsfw\ListNsfwFindingsRequest;
use App\Http\Resources\Collections\PaginatedNsfwFindingsResource;
use App\Http\Resources\Models\NsfwFindingResource;
use App\Models\NsfwDetection;
use App\Models\Photo;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

/**
 * NSFW findings review (Feature 089): one entry per photo with all its detections.
 */
class NsfwFindingsController extends Controller
{
	/**
	 * Photos with at least one detection, latest detection first (FR-089-09).
	 *
	 * Three steps with a constant number of queries: the page of photo ids,
	 * the photos (with thumbnails, owners and albums), the detections.
	 */
	public function index(ListNsfwFindingsRequest $request): PaginatedNsfwFindingsResource
	{
		/** @var \Illuminate\Pagination\LengthAwarePaginator<int,object{photo_id:string,latest_id:int}> $page */
		$page = DB::table('nsfw_detections')
			->select('photo_id', DB::raw('MAX(id) as latest_id'))
			->groupBy('photo_id')
			->orderBy('latest_id', 'desc')
			->paginate($request->per_page);

		/** @var string[] $photo_ids */
		$photo_ids = collect($page->items())->pluck('photo_id')->all();

		$photos = Photo::query()
			->with(['size_variants', 'owner', 'albums'])
			->whereIn('id', $photo_ids)
			->get()
			->keyBy('id');

		$detections = NsfwDetection::query()
			->whereIn('photo_id', $photo_ids)
			->orderBy('confidence', 'desc')
			->orderBy('id', 'desc')
			->get()
			->groupBy('photo_id');

		$entries = collect($photo_ids)
			->filter(fn (string $photo_id) => $photos->has($photo_id))
			->map(fn (string $photo_id) => new NsfwFindingResource($photos->get($photo_id), $detections->get($photo_id, collect())))
			->values();

		return new PaginatedNsfwFindingsResource($page, $entries);
	}

	/**
	 * Delete findings by detection id (FR-089-10, ADR-089-02).
	 * The photo state is unchanged and no moderation event is written.
	 */
	public function destroy(DeleteNsfwFindingsRequest $request): Response
	{
		NsfwDetection::query()->whereIn('id', $request->detectionIds())->delete();

		return response()->noContent();
	}

	/**
	 * Delete every finding of the given photos (FR-089-11, ADR-089-02).
	 * The photo state is unchanged and no moderation event is written.
	 */
	public function destroyByPhotos(DeleteNsfwFindingsByPhotosRequest $request): Response
	{
		NsfwDetection::query()->whereIn('photo_id', $request->photoIds())->delete();

		return response()->noContent();
	}
}
