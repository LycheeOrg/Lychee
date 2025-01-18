<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\HasPhotos;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\Authorize\AuthorizeCanEditPhotosTrait;
use App\Legacy\V1\Requests\Traits\HasPhotosTrait;
use App\Legacy\V1\RuleSets\Photo\SetPhotosStarredRuleSet;
use App\Models\Photo;

/**
 * Class SetPhotosStarredRequest.
 */
final class SetPhotosStarredRequest extends BaseApiRequest implements HasPhotos
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
		return SetPhotosStarredRuleSet::rules();
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
