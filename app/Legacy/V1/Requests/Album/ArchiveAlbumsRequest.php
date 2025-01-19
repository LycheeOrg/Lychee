<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Album;

use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\HasAlbums;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\HasAlbumsTrait;
use App\Policies\AlbumPolicy;
use App\Rules\AlbumIDListRule;
use Illuminate\Support\Facades\Gate;

/**
 * @implements HasAlbums<\App\Contracts\Models\AbstractAlbum>
 */
final class ArchiveAlbumsRequest extends BaseApiRequest implements HasAlbums
{
	/** @use HasAlbumsTrait<AbstractAlbum> */
	use HasAlbumsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		/** @var AbstractAlbum $album */
		foreach ($this->albums as $album) {
			if (!Gate::check(AlbumPolicy::CAN_ACCESS, $album)) {
				// @codeCoverageIgnoreStart
				return false;
				// @codeCoverageIgnoreEnd
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
			RequestAttribute::ALBUM_IDS_ATTRIBUTE => ['required', new AlbumIDListRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		// TODO: `App\Actions\Album\Archive::compressAlbum` iterates over the original size variant of each photo in the album; we should eagerly load them for higher efficiency.
		$this->albums = $this->albumFactory->findAbstractAlbumsOrFail(
			explode(',', $values[RequestAttribute::ALBUM_IDS_ATTRIBUTE])
		);
	}
}
