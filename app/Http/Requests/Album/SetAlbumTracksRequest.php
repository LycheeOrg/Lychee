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
use App\Rules\AlbumIDRule;
use Illuminate\Http\UploadedFile;

/**
 * v8 batch track upload (FR-055-06): `album_id` + `files[]` (1..N), each
 * validated identically to the legacy single-file rule.
 */
class SetAlbumTracksRequest extends BaseApiRequest implements HasAlbum
{
	use HasAlbumTrait;
	use AuthorizeCanEditAlbumTrait;

	public const FILES_ATTRIBUTE = 'files';

	/** @var UploadedFile[] */
	public array $uploaded_files = [];

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new AlbumIDRule(false)],
			self::FILES_ATTRIBUTE => ['required', 'array', 'min:1'],
			self::FILES_ATTRIBUTE . '.*' => 'required|file',
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

		$uploaded = $this->file(self::FILES_ATTRIBUTE);
		$this->uploaded_files = match (true) {
			is_array($uploaded) => $uploaded,
			$uploaded instanceof UploadedFile => [$uploaded],
			default => [],
		};
	}
}
