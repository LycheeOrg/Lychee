<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\HasBaseAlbum;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Legacy\V1\Requests\Traits\HasBaseAlbumTrait;
use App\Rules\RandomIDRule;

/**
 * Class SetAlbumNSFWRequest.
 *
 * @codeCoverageIgnore Legacy stuff
 */
final class SetAlbumNSFWRequest extends BaseApiRequest implements HasBaseAlbum
{
	use HasBaseAlbumTrait;
	use AuthorizeCanEditAlbumTrait;

	protected bool $isNSFW = false;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::IS_NSFW_ATTRIBUTE => 'required|boolean',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = $this->albumFactory->findBaseAlbumOrFail(
			$values[RequestAttribute::ALBUM_ID_ATTRIBUTE]
		);
		$this->isNSFW = static::toBoolean($values[RequestAttribute::IS_NSFW_ATTRIBUTE]);
	}

	public function isNSFW(): bool
	{
		return $this->isNSFW;
	}
}
