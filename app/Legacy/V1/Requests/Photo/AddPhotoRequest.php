<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\HasAbstractAlbum;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Legacy\V1\Requests\Traits\HasAbstractAlbumTrait;
use App\Legacy\V1\RuleSets\Photo\AddPhotoRuleSet;
use Illuminate\Http\UploadedFile;

final class AddPhotoRequest extends BaseApiRequest implements HasAbstractAlbum
{
	use HasAbstractAlbumTrait;
	use AuthorizeCanEditAlbumTrait;

	protected ?int $fileLastModifiedTime;
	protected UploadedFile $file;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return AddPhotoRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$albumID = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE];
		$this->album = $albumID === null ?
			null :
			$this->albumFactory->findAbstractAlbumOrFail($albumID);
		// Convert the File Last Modified to seconds instead of milliseconds
		$val = $values[RequestAttribute::FILE_LAST_MODIFIED_TIME] ?? null;
		$this->fileLastModifiedTime = $val !== null ? intval($val) : null;
		$this->file = $files[RequestAttribute::FILE_ATTRIBUTE];
	}

	public function uploadedFile(): UploadedFile
	{
		return $this->file;
	}

	public function fileLastModifiedTime(): ?int
	{
		return $this->fileLastModifiedTime !== null ? intval($this->fileLastModifiedTime / 1000) : null;
	}
}
