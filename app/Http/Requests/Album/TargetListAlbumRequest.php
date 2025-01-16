<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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
	/** @phpstan-use HasAlbumsTrait<Album> */
	use HasAlbumsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_EDIT_ID, [AbstractAlbum::class, $this->albums->map(fn (Album $album): string => $album->id)->toArray()]);
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
		/** @phpstan-ignore-next-line */
		$this->albums = $this->albumFactory->findAbstractAlbumsOrFail($album_ids);
	}
}
