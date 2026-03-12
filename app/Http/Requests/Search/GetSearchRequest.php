<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Search;

use App\Actions\Search\SearchTokenParser;
use App\Contracts\Http\Requests\HasAbstractAlbum;
use App\Contracts\Http\Requests\HasSearchTokens;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAbstractAlbumTrait;
use App\Http\Requests\Traits\HasSearchTokensTrait;
use App\Policies\AlbumPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use function Safe\base64_decode;

class GetSearchRequest extends BaseApiRequest implements HasAbstractAlbum, HasSearchTokens
{
	use HasSearchTokensTrait;
	use HasAbstractAlbumTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		if (!Auth::check() && !$this->configs()->getValueAsBool('search_public')) {
			return false;
		}

		return Gate::check(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, $this->album]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::TERM_ATTRIBUTE => ['required', 'string'],
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['sometimes', new RandomIDRule(true)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$album_id = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE] ?? null;
		$this->album = $this->album_factory->findNullalbleAbstractAlbumOrFail($album_id);

		$raw = base64_decode($values[RequestAttribute::TERM_ATTRIBUTE], true);
		$this->tokens = SearchTokenParser::parse($raw);
	}
}