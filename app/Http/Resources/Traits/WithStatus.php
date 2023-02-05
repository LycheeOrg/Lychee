<?php

namespace App\Http\Resources\Traits;

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