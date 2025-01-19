<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Import;

use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\HasAlbum;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Legacy\V1\Requests\Traits\HasAlbumTrait;
use App\Legacy\V1\RuleSets\Import\ImportFromUrlRuleSet;
use App\Models\Album;

final class ImportFromUrlRequest extends BaseApiRequest implements HasAlbum
{
	use HasAlbumTrait;
	use AuthorizeCanEditAlbumTrait;

	/**
	 * @var string[]
	 */
	protected array $urls = [];

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return ImportFromUrlRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var string|null */
		$albumID = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE];
		$this->album = $albumID === null ?
			null :
			// @codeCoverageIgnoreStart
			Album::query()->findOrFail($albumID);
		// @codeCoverageIgnoreEnd
		// The replacement below looks suspicious.
		// If it was really necessary, then there would be much more special
		// characters (e.i. for example umlauts in international domain names)
		// which would require replacement by their corresponding %-encoding.
		// However, I assume that the PHP method `fopen` is happily fine with
		// any character and internally handles special characters itself.
		// Hence, either use a proper encoding method here instead of our
		// home-brewed, poor-man replacement or drop it entirely.
		// TODO: Find out what is needed and proceed accordingly.
		$this->urls = str_replace(' ', '%20', $values[RequestAttribute::URLS_ATTRIBUTE]);
	}

	/**
	 * @return string[]
	 */
	public function urls(): array
	{
		return $this->urls;
	}
}
