<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\HasAlbum;
use App\Legacy\V1\Contracts\Http\Requests\HasPhotos;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\Authorize\AuthorizeCanEditPhotosAlbumTrait;
use App\Legacy\V1\Requests\Traits\HasAlbumTrait;
use App\Legacy\V1\Requests\Traits\HasPhotosTrait;
use App\Legacy\V1\RuleSets\Photo\MovePhotosRuleSet;
use App\Models\Album;
use App\Models\Photo;

final class MovePhotosRequest extends BaseApiRequest implements HasPhotos, HasAlbum
{
	use HasPhotosTrait;
	use HasAlbumTrait;
	use AuthorizeCanEditPhotosAlbumTrait;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return MovePhotosRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var array<int,string> $photo_ids */
		$photos_ids = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE];
		$this->photos = Photo::query()
			->findOrFail($photos_ids);
		/** @var string|null */
		$target_album_id = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE];
		$this->album = $target_album_id === null ? null : Album::query()->findOrFail($target_album_id);
	}
}
