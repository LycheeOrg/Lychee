<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\HasPhotos;
use App\Legacy\V1\Contracts\Http\Requests\HasTitle;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\Authorize\AuthorizeCanEditPhotosTrait;
use App\Legacy\V1\Requests\Traits\HasPhotosTrait;
use App\Legacy\V1\Requests\Traits\HasTitleTrait;
use App\Legacy\V1\RuleSets\Photo\SetPhotosTitleRuleSet;
use App\Models\Photo;

final class SetPhotosTitleRequest extends BaseApiRequest implements HasPhotos, HasTitle
{
	use HasPhotosTrait;
	use HasTitleTrait;
	use AuthorizeCanEditPhotosTrait;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return SetPhotosTitleRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var array<int,string> $photosIDs */
		$photosIDs = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE];
		$this->photos = Photo::query()->findOrFail($photosIDs);
		$this->title = $values[RequestAttribute::TITLE_ATTRIBUTE];
	}
}
