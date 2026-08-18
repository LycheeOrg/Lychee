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
 * Request validator for fetching paginated child/matching albums.
 *
 * Validates album_id and optional page parameter. Resolves to a real `Album`
 * (parent/child hierarchy), a `TagAlbum`, or a `PersonAlbum` — smart albums
 * (`BaseSmartAlbum`) have no "contained albums" concept and are intentionally
 * not resolved here.
 */
class GetAlbumChildrenRequest extends BaseApiRequest implements HasAbstractAlbum
{
	use HasAbstractAlbumTrait;

	protected int $page = 1;

	/**
	 * {@inheritDoc}
	 *
	 * We directly return the Gate check result here. If the album is password-protected,
	 * the password handling is done on a different request (::head)
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, $this->album]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::PAGE_ATTRIBUTE => ['sometimes', 'integer', 'min:1'],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->page = intval($values[RequestAttribute::PAGE_ATTRIBUTE] ?? 1);

		// Load album without unnecessary relations for this request
		$this->album = Album::without([
			'cover', 'cover.size_variants',
			'min_privilege_cover', 'min_privilege_cover.size_variants',
			'max_privilege_cover', 'max_privilege_cover.size_variants',
			'thumb',
			'owner',
			'statistics',
		])->find($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]);

		// Load tag album if not found as regular album
		$this->album ??= TagAlbum::find($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]);

		// Load person album if not found as tag album
		$this->album ??= PersonAlbum::find($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]);

		// If neither found, throw ModelNotFoundException
		$this->album ??= throw new ModelNotFoundException();
	}

	public function page(): int
	{
		return $this->page;
	}
}
