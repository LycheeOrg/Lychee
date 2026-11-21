<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Rules\RandomIDRule;

/**
 * Request for `GET /api/v2/Photo::uploadStatus` (Feature 116, FR-116-06).
 *
 * `photo_ids[]` holds 1 to 100 provisional IDs returned by the upload.
 * Open to every caller: access is decided per entry (ADR-116-01).
 */
class GetUploadStatusRequest extends BaseApiRequest
{
	public const MAX_IDS = 100;

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
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::PHOTO_IDS_ATTRIBUTE => ['required', 'array', 'min:1', 'max:' . self::MAX_IDS],
			RequestAttribute::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var string[] $photo_ids */
		$photo_ids = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE];
		$this->photo_ids = array_values($photo_ids);
	}
}
