<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Resources\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceResponse;

trait WithStatus
{
	private int $status = 200;

	public function setStatus(int $status): self
	{
		$this->status = $status;

		return $this;
	}

	/**
	 * @param Request $request
	 *
	 * @return JsonResponse
	 */
	public function toResponse($request): JsonResponse
	{
		return (new ResourceResponse($this))->toResponse($request)->setStatusCode($this->status);
	}
}