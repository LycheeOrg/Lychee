<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasAlbum;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Models\Album;
use App\Models\Track;
use App\Rules\AlbumIDRule;

/**
 * v8 track rename (FR-055-07): `album_id` + `track_id` + `name`.
 */
class RenameAlbumTrackRequest extends BaseApiRequest implements HasAlbum
{
	use HasAlbumTrait;
	use AuthorizeCanEditAlbumTrait;

	public Track $track;
	public string $name;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new AlbumIDRule(false)],
			RequestAttribute::TRACK_ID_ATTRIBUTE => ['required', 'integer'],
			RequestAttribute::NAME_ATTRIBUTE => ['required', 'string', 'max:255'],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var string $album_id */
		$album_id = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE];
		$this->album = Album::query()->findOrFail($album_id);

		/** @var int $track_id */
		$track_id = $values[RequestAttribute::TRACK_ID_ATTRIBUTE];
		$this->track = $this->album->tracks()->where('id', '=', $track_id)->firstOrFail();

		/** @var string $name */
		$name = $values[RequestAttribute::NAME_ATTRIBUTE];
		$this->name = $name;
	}
}
