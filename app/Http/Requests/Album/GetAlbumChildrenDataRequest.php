<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasAbstractAlbum;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAbstractAlbumTrait;
use App\Models\Album;
use App\Models\PersonAlbum;
use App\Models\TagAlbum;
use App\Policies\AlbumPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

/**
 * Request for `GET /api/v3/Albums/{album_id}/children`.
 * `album_id` is bound from the route segment via
 * {@see self::prepareForValidation()} (mirrors
 * {@see \App\Http\Requests\Album\GetAlbumBucketsRequest}'s pattern), but —
 * unlike that endpoint — resolves to a real {@see Album} *or* a
 * {@see TagAlbum}/{@see PersonAlbum} ("matching albums" listing), mirroring
 * {@see \App\Http\Requests\Album\GetAlbumChildrenRequest}'s (v2) resolution
 * exactly: smart albums have no "contained albums" concept and are
 * intentionally not resolved here.
 */
class GetAlbumChildrenDataRequest extends BaseApiRequest implements HasAbstractAlbum
{
	use HasAbstractAlbumTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return config('features.struct-of-array') === true &&
			Gate::check(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, $this->album]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function prepareForValidation(): void
	{
		/** @disregard */
		$this->merge([
			RequestAttribute::ALBUM_ID_ATTRIBUTE => $this->route(RequestAttribute::ALBUM_ID_ATTRIBUTE),
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var string $album_id */
		$album_id = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE];

		$this->album = Album::find($album_id);
		$this->album ??= TagAlbum::find($album_id);
		$this->album ??= PersonAlbum::find($album_id);
		$this->album ??= throw new ModelNotFoundException();
	}
}
