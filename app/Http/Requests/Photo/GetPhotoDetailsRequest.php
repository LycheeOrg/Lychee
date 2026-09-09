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
 * Request for `GET /api/v3/Albums/{album_id}/Photos/details`. Validates
 * exactly-one-of `bucket_id` (string, uncapped result set — a large bucket
 * returns everything it contains, no truncation, per explicit user
 * direction) / `photo_ids[]` (array, capped at 300 entries as input — 422
 * above). `album_id` resolution mirrors
 * {@see \App\Http\Requests\Photo\GetPhotoBucketsRequest} exactly (regular
 * `Album` only).
 */
class GetPhotoDetailsRequest extends BaseApiRequest
{
	private Album $album;
	private ?string $bucket_id = null;
	/** @var string[]|null */
	private ?array $photo_ids = null;

	public function album(): Album
	{
		return $this->album;
	}

	public function bucketId(): ?string
	{
		return $this->bucket_id;
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
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::BUCKET_ID_ATTRIBUTE => [
				'required_without:' . RequestAttribute::PHOTO_IDS_ATTRIBUTE,
				'prohibits:' . RequestAttribute::PHOTO_IDS_ATTRIBUTE,
				'nullable',
				'string',
			],
			RequestAttribute::PHOTO_IDS_ATTRIBUTE => [
				'required_without:' . RequestAttribute::BUCKET_ID_ATTRIBUTE,
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
		$this->album = Album::query()->where('id', '=', $album_id)->firstOrFail();

		/** @var string|null $bucket_id */
		$bucket_id = $values[RequestAttribute::BUCKET_ID_ATTRIBUTE] ?? null;
		$this->bucket_id = $bucket_id;

		/** @var string[]|null $photo_ids */
		$photo_ids = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE] ?? null;
		$this->photo_ids = $photo_ids;
	}
}
