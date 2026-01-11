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
use App\Enum\SmartAlbumType;
use App\Factories\AlbumFactory;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAbstractAlbumTrait;
use App\Models\Album;
use App\Models\TagAlbum;
use App\Policies\AlbumPolicy;
use App\Rules\AlbumIDRule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

/**
 * Request validator for fetching paginated photos.
 *
 * Validates album_id and optional page parameter.
 */
class GetAlbumPhotosRequest extends BaseApiRequest implements HasAbstractAlbum
{
	use HasAbstractAlbumTrait;

	protected int $page = 1;

	/**
	 * {@inheritDoc}
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
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new AlbumIDRule(false)],
			RequestAttribute::PAGE_ATTRIBUTE => ['sometimes', 'integer', 'min:1'],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->page = intval($values[RequestAttribute::PAGE_ATTRIBUTE] ?? 1);

		$smart_id = SmartAlbumType::tryFrom($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]);

		if ($smart_id !== null) {
			$this->album = resolve(AlbumFactory::class)->createSmartAlbum($smart_id, true);

			return;
		}

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

		// If neither found, throw ModelNotFoundException
		$this->album ??= throw new ModelNotFoundException();
	}

	public function page(): int
	{
		return $this->page;
	}
}
