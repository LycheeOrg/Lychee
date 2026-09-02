<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;

/**
 * Request shared by `AlbumCategoryController::smart()`/`tags()`/`tagsRights()`
 * (Feature 062, FR-062-01/FR-062-12) — no `scope` parameter (un-scoped, per
 * FR-062-09), no album to resolve; just the feature flag gate. `persons()`
 * and `pinned()` instead consume {@see GetScopedAlbumsRequest}.
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
