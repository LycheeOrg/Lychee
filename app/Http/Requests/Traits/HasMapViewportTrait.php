<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\Contracts\Http\Requests\RequestAttribute;
use App\DTO\MapViewport;

/**
 * Shared `north`/`south`/`east`/`west`/`zoom` validation + resolution
 * (FR-067-01, FR-067-02), used by `GetMapBucketsRequest`/`GetMapPhotosRequest`.
 */
trait HasMapViewportTrait
{
	protected MapViewport $viewport;

	public function viewport(): MapViewport
	{
		return $this->viewport;
	}

	/**
	 * @return array<string,array<int,string>>
	 */
	protected function mapViewportRules(): array
	{
		return [
			RequestAttribute::NORTH_ATTRIBUTE => ['required', 'numeric', 'between:-90,90'],
			RequestAttribute::SOUTH_ATTRIBUTE => ['required', 'numeric', 'between:-90,90'],
			RequestAttribute::EAST_ATTRIBUTE => ['required', 'numeric', 'between:-180,180'],
			RequestAttribute::WEST_ATTRIBUTE => ['required', 'numeric', 'between:-180,180'],
			RequestAttribute::ZOOM_ATTRIBUTE => ['required', 'integer', 'between:0,24'],
		];
	}

	/**
	 * @param array<string,mixed> $values
	 */
	protected function resolveViewport(array $values): void
	{
		$this->viewport = new MapViewport(
			north: (float) $values[RequestAttribute::NORTH_ATTRIBUTE],
			south: (float) $values[RequestAttribute::SOUTH_ATTRIBUTE],
			east: (float) $values[RequestAttribute::EAST_ATTRIBUTE],
			west: (float) $values[RequestAttribute::WEST_ATTRIBUTE],
			zoom: (int) $values[RequestAttribute::ZOOM_ATTRIBUTE],
		);
	}
}
