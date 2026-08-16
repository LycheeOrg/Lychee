<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Requests\LandingLink\DestroyLandingLinkRequest;
use App\Http\Requests\LandingLink\IndexLandingLinkRequest;
use App\Http\Requests\LandingLink\PatchLandingLinkRequest;
use App\Http\Requests\LandingLink\ReorderLandingLinkRequest;
use App\Http\Requests\LandingLink\ShowLandingLinkRequest;
use App\Http\Requests\LandingLink\StoreLandingLinkRequest;
use App\Http\Requests\LandingLink\UpdateLandingLinkRequest;
use App\Http\Resources\Collections\LandingLinkCollection;
use App\Http\Resources\Models\LandingLinkResource;
use App\Models\LandingLink;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LandingLinkController extends Controller
{
	public function index(IndexLandingLinkRequest $request): LandingLinkCollection
	{
		$landing_links = LandingLink::query()->orderBy('sort_order')->orderBy('created_at')->get();

		return new LandingLinkCollection($landing_links);
	}

	public function store(StoreLandingLinkRequest $request): LandingLinkResource
	{
		$validated = $request->validated();

		/** @var LandingLink $landing_link */
		$landing_link = LandingLink::create($validated);

		return new LandingLinkResource($landing_link);
	}

	public function show(ShowLandingLinkRequest $request): LandingLinkResource
	{
		return new LandingLinkResource($request->landing_link);
	}

	public function update(UpdateLandingLinkRequest $request): LandingLinkResource
	{
		$validated = $request->validated();
		unset($validated['landing_link_id']);
		$request->landing_link->fill($validated)->save();

		return new LandingLinkResource($request->landing_link);
	}

	public function patch(PatchLandingLinkRequest $request): LandingLinkResource
	{
		$validated = $request->validated();
		unset($validated['landing_link_id']);
		$request->landing_link->fill($validated)->save();

		return new LandingLinkResource($request->landing_link);
	}

	public function destroy(DestroyLandingLinkRequest $request): void
	{
		$request->landing_link->delete();
	}

	public function reorder(ReorderLandingLinkRequest $request): LandingLinkCollection
	{
		$existing_ids = LandingLink::query()->pluck('id')->sort()->values()->all();
		$submitted_ids = collect($request->ids)->sort()->values()->all();

		if ($existing_ids !== $submitted_ids) {
			throw ValidationException::withMessages(['ids' => 'The submitted ids must be the complete set of existing LandingLink ids.']);
		}

		DB::transaction(function () use ($request): void {
			foreach ($request->ids as $index => $id) {
				LandingLink::query()->whereKey($id)->update(['sort_order' => $index]);
			}
		});

		$landing_links = LandingLink::query()->orderBy('sort_order')->orderBy('created_at')->get();

		return new LandingLinkCollection($landing_links);
	}
}
