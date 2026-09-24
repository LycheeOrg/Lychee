<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Search;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Rules\RandomIDRule;

/**
 * Request for `GET /api/v3/Search/Photos/details` (Feature 069, FR-069-06).
 *
 * Adds the `photo_ids[]` scope to {@see GetSearchV3Request}'s shared params.
 * Capped at 300 entries as *input* (422 above), matching Feature 064's own
 * `details` cap (Q-064-04) — unlike that endpoint there is no `bucket_id`
 * alternative, because search has no bucket tier at all (spec.md NG1).
 */
class GetSearchV3DetailsRequest extends GetSearchV3Request
{
	/** @var string[] */
	private array $photo_ids = [];

	/**
	 * @return string[]
	 */
	public function photoIds(): array
	{
		return $this->photo_ids;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return array_merge(parent::rules(), [
			RequestAttribute::PHOTO_IDS_ATTRIBUTE => ['required', 'array', 'max:300'],
			RequestAttribute::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		parent::processValidatedValues($values, $files);

		/** @var string[] $photo_ids */
		$photo_ids = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE] ?? [];
		$this->photo_ids = $photo_ids;
	}
}
