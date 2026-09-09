<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;

/**
 * Request for `GET /api/v3/Albums/{album_id}/Photos/buckets`.
 * `album_id` is bound from the route segment via
 * {@see self::prepareForValidation()}, mirroring
 * {@see \App\Http\Requests\Album\GetAlbumBucketsRequest}'s pattern.
 * Resolves to a regular {@see Album} only — a `TagAlbum`/`PersonAlbum` id
 * or an unresolved id both yield a 404
 * ({@see \Illuminate\Database\Eloquent\ModelNotFoundException}), never the
 * broader `AlbumFactory::findAbstractAlbumOrFail()` resolution.
 */
class GetPhotoBucketsRequest extends BaseApiRequest
{
	private Album $album;

	public function album(): Album
	{
		return $this->album;
	}

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
		$this->album = Album::query()->where('id', '=', $album_id)->firstOrFail();
	}
}
