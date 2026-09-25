<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasAlbums;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAlbumsTrait;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use App\Rules\AlbumIDRule;
use Illuminate\Support\Facades\Gate;

/**
 * @implements HasAlbums<Album>
 */
class TargetListAlbumRequest extends BaseApiRequest implements HasAlbums
{
	/** @use HasAlbumsTrait<Album> */
	use HasAlbumsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		// Feature 072 (FR-072-13): the source albums must be movable, not merely editable.
		// Photo pickers pass no source album.
		if ($this->albums->isEmpty()) {
			return true;
		}

		return Gate::check(AlbumPolicy::CAN_MOVE_ALBUMS_ID, [AbstractAlbum::class, $this->albums->map(fn (Album $album): string => $album->id)->all()]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_IDS_ATTRIBUTE => 'sometimes|array|min:1',
			RequestAttribute::ALBUM_IDS_ATTRIBUTE . '.*' => ['required', new AlbumIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$album_ids = $values[RequestAttribute::ALBUM_IDS_ATTRIBUTE] ?? [];
		$this->albums = $this->album_factory->findBaseAlbumsOrFail($album_ids, albums_only: true);
	}
}
