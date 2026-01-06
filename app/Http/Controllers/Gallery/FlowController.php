<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Actions\Albums\Flow;
use App\Http\Requests\Flow\FlowRequest;
use App\Http\Resources\Flow\FlowResource;
use App\Http\Resources\Flow\InitResource;
use Illuminate\Routing\Controller;
use Spatie\LaravelData\Data;

class FlowController extends Controller
{
	/**
	 * Handle the incoming request.
	 *
	 * @param FlowRequest $request
	 */
	public function __invoke(FlowRequest $request, Flow $flow)
	{
		$pagination_limit = $request->configs()->getValueAsInt('flow_max_items');
		$album_results = $flow->do()->paginate($pagination_limit);

		return FlowResource::fromData($album_results);
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
}
