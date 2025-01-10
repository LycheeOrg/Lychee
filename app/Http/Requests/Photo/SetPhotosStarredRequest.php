<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasPhotos;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditPhotosTrait;
use App\Http\Requests\Traits\HasPhotosTrait;
use App\Models\Photo;
use App\Rules\RandomIDRule;

/**
 * Class SetPhotosStarredRequest.
 */
class SetPhotosStarredRequest extends BaseApiRequest implements HasPhotos
{
	use HasPhotosTrait;
	use AuthorizeCanEditPhotosTrait;

	public const IS_STARRED_ATTRIBUTE = 'is_starred';

	protected bool $isStarred = false;

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
		/** @var array<int,string> $photosIDs */
		$photosIDs = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE];
		$this->photos = Photo::query()->findOrFail($photosIDs);
		$this->isStarred = static::toBoolean($values[RequestAttribute::IS_STARRED_ATTRIBUTE]);
	}

	public function isStarred(): bool
	{
		return $this->isStarred;
	}
}
