<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Requests\LandingFeaturedItem\DestroyLandingFeaturedItemRequest;
use App\Http\Requests\LandingFeaturedItem\IndexLandingFeaturedItemRequest;
use App\Http\Requests\LandingFeaturedItem\PatchLandingFeaturedItemRequest;
use App\Http\Requests\LandingFeaturedItem\ReorderLandingFeaturedItemRequest;
use App\Http\Requests\LandingFeaturedItem\ShowLandingFeaturedItemRequest;
use App\Http\Requests\LandingFeaturedItem\StoreLandingFeaturedItemRequest;
use App\Http\Requests\LandingFeaturedItem\UpdateLandingFeaturedItemRequest;
use App\Http\Resources\Collections\LandingFeaturedItemCollection;
use App\Http\Resources\Models\LandingFeaturedItemResource;
use App\Models\LandingFeaturedItem;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LandingFeaturedItemController extends Controller
{
	public function index(IndexLandingFeaturedItemRequest $request): LandingFeaturedItemCollection
	{
		$landing_featured_items = LandingFeaturedItem::query()->orderBy('sort_order')->orderBy('created_at')->get();

		return new LandingFeaturedItemCollection($landing_featured_items);
	}

	public function store(StoreLandingFeaturedItemRequest $request): LandingFeaturedItemResource
	{
		$validated = $request->validated();

		/** @var LandingFeaturedItem $landing_featured_item */
		$landing_featured_item = LandingFeaturedItem::create($validated);

		return new LandingFeaturedItemResource($landing_featured_item);
	}

	public function show(ShowLandingFeaturedItemRequest $request): LandingFeaturedItemResource
	{
		return new LandingFeaturedItemResource($request->landing_featured_item);
	}

	public function update(UpdateLandingFeaturedItemRequest $request): LandingFeaturedItemResource
	{
		$validated = $request->validated();
		unset($validated['landing_featured_item_id']);
		$request->landing_featured_item->fill($validated)->save();

		return new LandingFeaturedItemResource($request->landing_featured_item);
	}

	public function patch(PatchLandingFeaturedItemRequest $request): LandingFeaturedItemResource
	{
		$validated = $request->validated();
		unset($validated['landing_featured_item_id']);
		$request->landing_featured_item->fill($validated)->save();

		return new LandingFeaturedItemResource($request->landing_featured_item);
	}

	public function destroy(DestroyLandingFeaturedItemRequest $request): void
	{
		$request->landing_featured_item->delete();
	}

	public function reorder(ReorderLandingFeaturedItemRequest $request): LandingFeaturedItemCollection
	{
		$existing_ids = LandingFeaturedItem::query()->pluck('id')->sort()->values()->all();
		$submitted_ids = collect($request->ids)->sort()->values()->all();

		if ($existing_ids !== $submitted_ids) {
			throw ValidationException::withMessages(['ids' => 'The submitted ids must be the complete set of existing LandingFeaturedItem ids.']);
		}

		DB::transaction(function () use ($request): void {
			foreach ($request->ids as $index => $id) {
				LandingFeaturedItem::query()->whereKey($id)->update(['sort_order' => $index]);
			}
		});

		$landing_featured_items = LandingFeaturedItem::query()->orderBy('sort_order')->orderBy('created_at')->get();

		return new LandingFeaturedItemCollection($landing_featured_items);
	}
}
