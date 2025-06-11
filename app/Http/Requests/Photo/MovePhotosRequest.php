<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasAlbum;
use App\Contracts\Http\Requests\HasFromAlbum;
use App\Contracts\Http\Requests\HasPhotos;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditPhotosAlbumTrait;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Http\Requests\Traits\HasFromAlbumTrait;
use App\Http\Requests\Traits\HasPhotosTrait;
use App\Models\Album;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use App\Rules\AlbumIDRule;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;

class MovePhotosRequest extends BaseApiRequest implements HasPhotos, HasAlbum, HasFromAlbum
{
	use HasPhotosTrait;
	use HasAlbumTrait;
	use HasFromAlbumTrait;
	use AuthorizeCanEditPhotosAlbumTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		if (!Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $this->album])) {
			return false;
		}

		if (!Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $this->from_album])) {
			return false;
		}

		// TODO: refactor this check so it does not explode.
		/** @var Photo $photo */
		foreach ($this->photos as $photo) {
			if (!Gate::check(PhotoPolicy::CAN_EDIT, $photo)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::PHOTO_IDS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
			RequestAttribute::FROM_ID_ATTRIBUTE => ['present', new AlbumIDRule(true)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var array<int,string> $photos_ids */
		$photos_ids = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE];
		$this->photos = Photo::query()
			->findOrFail($photos_ids);

		/** @var string|null */
		$target_album_id = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE];
		$this->album = $target_album_id === null ? null : Album::query()->findOrFail($target_album_id);

		$this->from_album = $this->album_factory->findNullalbleAbstractAlbumOrFail($values[RequestAttribute::FROM_ID_ATTRIBUTE]);
	}
}