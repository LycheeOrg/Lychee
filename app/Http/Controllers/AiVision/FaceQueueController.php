<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\AiVision;

use App\Actions\Face\QueryFaceQueue;
use App\Http\Requests\Face\GetFaceQueueRequest;
use App\Http\Resources\V3\FaceQueueResource;
use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * `GET /api/v3/Faces/queue` (Feature 093, API-093-03).
 */
class FaceQueueController extends Controller
{
	public function index(GetFaceQueueRequest $request, QueryFaceQueue $query_face_queue): FaceQueueResource
	{
		/** @var User $user */
		$user = Auth::user();

		return $query_face_queue->do(
			$user,
			$request->clusterLabel(),
			$request->configs()->getValueAsInt('ai_vision_face_queue_limit'),
			$request->seen(),
		);
	}
}
