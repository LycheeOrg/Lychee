<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Person;

use App\Contracts\Http\Requests\HasAbstractAlbum;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAbstractAlbumTrait;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use App\Rules\AlbumIDRule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

/**
 * Request validator for fetching persons in a specific album.
 *
 * Validates album_id and checks album access permission.
 */
class GetAlbumPersonsRequest extends BaseApiRequest implements HasAbstractAlbum
{
	use HasAbstractAlbumTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, $this->album]) &&
			Gate::check(AlbumPolicy::CAN_VIEW_ALBUM_PEOPLE, [AbstractAlbum::class, $this->album]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new AlbumIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		// Load album without unnecessary relations for this request
		$this->album = Album::without([
			'cover', 'cover.size_variants',
			'min_privilege_cover', 'min_privilege_cover.size_variants',
			'max_privilege_cover', 'max_privilege_cover.size_variants',
			'thumb',
			'owner',
			'statistics',
		])->find($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]);

		// If not found, throw ModelNotFoundException
		$this->album ??= throw new ModelNotFoundException();
	}
}
