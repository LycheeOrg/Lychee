<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;

/**
 * Request shared by `AlbumSmartController::smart()` and
 * `AlbumTagController::tags()`/`tagsRights()` — no `scope` parameter, no
 * album to resolve; just the feature flag gate. `persons()` and `pinned()`
 * instead consume {@see GetScopedAlbumsRequest}.
 */
class GetAlbumCategoryRequest extends BaseApiRequest
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return config('features.struct-of-array') === true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		// Nothing to process — no parameters.
	}
}
