<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasAlbum;
use App\Contracts\Http\Requests\HasAlbums;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumAlbumsTrait;
use App\Http\Requests\Traits\HasAlbumsTrait;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Models\Album;
use App\Rules\AlbumIDRule;
use App\Rules\RandomIDRule;
use Illuminate\Support\Collection;

/**
 * @implements HasAlbums<Album>
 */
class MergeAlbumsRequest extends BaseApiRequest implements HasAlbum, HasAlbums
{
	use HasAlbumTrait;
	/** @phpstan-use HasAlbumsTrait<Album> */
	use HasAlbumsTrait;
	use AuthorizeCanEditAlbumAlbumsTrait;

	public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
	{
		parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);

		$this->albums = new Collection(); // initialize with empty collection to avoid error MergeAlbumsRequest::$albums must not be accessed before initialization
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::ALBUM_IDS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::ALBUM_IDS_ATTRIBUTE . '.*' => ['required', new AlbumIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var string $id */
		$id = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE];

		/** @var array<int,string> $ids */
		$ids = $values[RequestAttribute::ALBUM_IDS_ATTRIBUTE];

		/** @var Album $album */
		$album = Album::query()->findOrFail($id);
		$this->album = $album;

		/** @var Collection<int,Album> */
		$albums = Album::query()->with(['children'])->findOrFail($ids)->toBase(); /** @phpstan-ignore varTag.type */
		$this->albums = $albums;
	}
}