<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasPhotos;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanStarPhotosTrait;
use App\Http\Requests\Traits\HasPhotosTrait;
use App\Models\Photo;
use App\Rules\RandomIDRule;

/**
 * Class SetPhotosStarredRequest.
 */
class SetPhotosStarredRequest extends BaseApiRequest implements HasPhotos
{
	use HasPhotosTrait;
	use AuthorizeCanStarPhotosTrait;

	public const IS_STARRED_ATTRIBUTE = 'is_starred';

	protected bool $is_starred = false;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::PHOTO_IDS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
			RequestAttribute::IS_STARRED_ATTRIBUTE => 'required|boolean',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var array<int,string> $photos_ids */
		$photos_ids = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE];
		$this->photos = Photo::query()
			->with(['size_variants', 'albums'])
			->findOrFail($photos_ids);
		$this->is_starred = static::toBoolean($values[RequestAttribute::IS_STARRED_ATTRIBUTE]);
	}

	public function isStarred(): bool
	{
		return $this->is_starred;
	}
}
