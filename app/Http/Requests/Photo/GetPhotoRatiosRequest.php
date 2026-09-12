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
 * Request for `GET /api/v3/Albums/{album_id}/Photos` — mirrors
 * {@see \App\Http\Requests\Photo\GetPhotoBucketsRequest} exactly. `album_id`
 * resolves via {@see \App\Factories\AlbumFactory::findAbstractAlbumOrFail()},
 * covering a regular {@see \App\Models\Album}, a
 * {@see \App\Models\TagAlbum}, a {@see \App\Models\PersonAlbum}, or a
 * {@see \App\SmartAlbums\BaseSmartAlbum}.
 *
 * Optional, mutually exclusive `bucket_ids[]`/`photo_ids[]` scoping
 * (FR-066-06) - mirrors {@see \App\Http\Requests\Photo\GetPhotoDetailsRequest}'s
 * `bucket_id`/`photo_ids[]` pair, minus `required_without`: unlike `details`,
 * omitting both here is meaningful (today's whole-scope behaviour,
 * NFR-066-03), not an error.
 */
class GetPhotoRatiosRequest extends BaseApiRequest implements HasAbstractAlbum
{
	use HasAbstractAlbumTrait;

	/** @var string[]|null */
	private ?array $bucket_ids = null;
	/** @var string[]|null */
	private ?array $photo_ids = null;

	/**
	 * @return string[]|null
	 */
	public function bucketIds(): ?array
	{
		return $this->bucket_ids;
	}

	/**
	 * @return string[]|null
	 */
	public function photoIds(): ?array
	{
		return $this->photo_ids;
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
			RequestAttribute::BUCKET_IDS_ATTRIBUTE => [
				'sometimes',
				'prohibits:' . RequestAttribute::PHOTO_IDS_ATTRIBUTE,
				'array',
			],
			RequestAttribute::BUCKET_IDS_ATTRIBUTE . '.*' => ['required', 'string'],
			RequestAttribute::PHOTO_IDS_ATTRIBUTE => [
				'sometimes',
				'prohibits:' . RequestAttribute::BUCKET_IDS_ATTRIBUTE,
				'array',
				'max:300',
			],
			RequestAttribute::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
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

		/** @var string[]|null $bucket_ids */
		$bucket_ids = $values[RequestAttribute::BUCKET_IDS_ATTRIBUTE] ?? null;
		$this->bucket_ids = $bucket_ids;

		/** @var string[]|null $photo_ids */
		$photo_ids = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE] ?? null;
		$this->photo_ids = $photo_ids;
	}
}
