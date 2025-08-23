<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasBaseAlbum;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasBaseAlbumTrait;
use App\Policies\AlbumPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;

class WatermarkAlbumRequest extends BaseApiRequest implements HasBaseAlbum
{
	use HasBaseAlbumTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $this->album]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(true)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = $this->album_factory->findBaseAlbumOrFail($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]);
	}
}
