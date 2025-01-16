<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasAlbum;
use App\Contracts\Http\Requests\HasCompactBoolean;
use App\Contracts\Http\Requests\HasPhoto;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Http\Requests\Traits\HasCompactBooleanTrait;
use App\Http\Requests\Traits\HasPhotoTrait;
use App\Models\Album;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SetAsHeaderRequest extends BaseApiRequest implements HasAlbum, HasPhoto, HasCompactBoolean
{
	use HasAlbumTrait;
	use HasPhotoTrait;
	use HasCompactBooleanTrait;

	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $this->album]) &&
		($this->is_compact || ($this->photo->album_id === $this->album->id));
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::HEADER_ID_ATTRIBUTE => ['required', new RandomIDRule(true)],
			RequestAttribute::IS_COMPACT_ATTRIBUTE => ['required', 'boolean'],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$album = $this->albumFactory->findBaseAlbumOrFail(
			$values[RequestAttribute::ALBUM_ID_ATTRIBUTE]
		);

		if (!$album instanceof Album) {
			throw ValidationException::withMessages([RequestAttribute::ALBUM_ID_ATTRIBUTE => 'album type not supported.']);
		}

		$this->album = $album;
		$this->is_compact = static::toBoolean($values[RequestAttribute::IS_COMPACT_ATTRIBUTE]);

		if ($this->is_compact) {
			return;
		}

		/** @var string $photoId */
		$photoId = $values[RequestAttribute::HEADER_ID_ATTRIBUTE];
		$this->photo = Photo::query()->findOrFail($photoId);
	}
}
