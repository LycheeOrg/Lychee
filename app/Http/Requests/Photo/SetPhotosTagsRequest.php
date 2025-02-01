<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasPhotos;
use App\Contracts\Http\Requests\HasTags;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditPhotosTrait;
use App\Http\Requests\Traits\HasPhotosTrait;
use App\Http\Requests\Traits\HasTagsTrait;
use App\Models\Photo;
use App\Rules\RandomIDRule;

class SetPhotosTagsRequest extends BaseApiRequest implements HasPhotos, HasTags
{
	use HasPhotosTrait;
	use HasTagsTrait;
	use AuthorizeCanEditPhotosTrait;

	public bool $shallOverride;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::SHALL_OVERRIDE_ATTRIBUTE => 'required|boolean',
			RequestAttribute::PHOTO_IDS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
			RequestAttribute::TAGS_ATTRIBUTE => 'present|array',
			RequestAttribute::TAGS_ATTRIBUTE . '.*' => 'required|string|min:1',
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
		$this->tags = $values[RequestAttribute::TAGS_ATTRIBUTE];
		$this->shallOverride = static::toBoolean($values[RequestAttribute::SHALL_OVERRIDE_ATTRIBUTE]);
	}
}