<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasAlbum;
use App\Contracts\Http\Requests\HasTags;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Http\Requests\Traits\HasTagsTrait;
use App\Models\Album;
use App\Rules\RandomIDRule;

class SetAlbumTagsRequest extends BaseApiRequest implements HasAlbum, HasTags
{
	use HasAlbumTrait;
	use HasTagsTrait;
	use AuthorizeCanEditAlbumTrait;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::TAGS_ATTRIBUTE => 'present|array',
			RequestAttribute::TAGS_ATTRIBUTE . '.*' => 'required|string|min:1',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = Album::query()
			->with('tags')
			->findOrFail($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]);
		$this->tags = $values[RequestAttribute::TAGS_ATTRIBUTE];
	}
}
