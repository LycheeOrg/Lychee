<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasAbstractAlbum;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAbstractAlbumTrait;
use App\Policies\AlbumPolicy;
use App\Rules\AlbumIDRule;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;

/**
 * Request for `GET /api/v3/Albums/{album_id}/Photos/urls` (Feature 102,
 * FR-102-05). `photo_ids[]` is required, 1 to 300 entries; the album is
 * resolved and authorized like {@see GetPhotoDetailsRequest}.
 */
class GetPhotoUrlsRequest extends BaseApiRequest implements HasAbstractAlbum
{
	use HasAbstractAlbumTrait;

	/** @var string[] */
	private array $photo_ids = [];

	/** Screen density of the grid: 1 or 2 (FR-102-05). */
	private int $density = 2;

	/**
	 * @return string[]
	 */
	public function photoIds(): array
	{
		return $this->photo_ids;
	}

	public function density(): int
	{
		return $this->density;
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
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new AlbumIDRule(false)],
			RequestAttribute::PHOTO_IDS_ATTRIBUTE => ['required', 'array', 'min:1', 'max:300'],
			RequestAttribute::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
			RequestAttribute::DENSITY_ATTRIBUTE => ['sometimes', 'integer', 'in:1,2'],
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
		// We do not need the relations. We try to be lean.
		$this->album = $this->album_factory->findAbstractAlbumOrFail($album_id, false);

		/** @var string[] $photo_ids */
		$photo_ids = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE];
		$this->photo_ids = $photo_ids;
		$this->density = intval($values[RequestAttribute::DENSITY_ATTRIBUTE] ?? 2);
	}
}
