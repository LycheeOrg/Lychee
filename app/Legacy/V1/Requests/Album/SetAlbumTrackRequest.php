<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\HasAlbum;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Legacy\V1\Requests\Traits\HasAlbumTrait;
use App\Models\Album;
use App\Rules\AlbumIDRule;
use Illuminate\Http\UploadedFile;

/**
 * @codeCoverageIgnore Legacy stuff
 */
final class SetAlbumTrackRequest extends BaseApiRequest implements HasAlbum
{
	use HasAlbumTrait;
	use AuthorizeCanEditAlbumTrait;

	public const FILE_ATTRIBUTE = 'file';
	public UploadedFile $file;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new AlbumIDRule(false)],
			self::FILE_ATTRIBUTE => 'required|file',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var string|null */
		$albumID = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE];
		$this->album = Album::query()->findOrFail($albumID);
		$this->file = $files[self::FILE_ATTRIBUTE];
	}

	public function uploadedFile(): UploadedFile
	{
		return $this->file;
	}
}
