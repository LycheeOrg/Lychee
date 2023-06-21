<?php

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasAbstractAlbum;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Http\Requests\Traits\HasAbstractAlbumTrait;
use App\Http\RuleSets\Photo\AddPhotoRuleSet;
use Illuminate\Http\UploadedFile;

class AddPhotoRequest extends BaseApiRequest implements HasAbstractAlbum
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
		$this->fileLastModifiedTime = $values[RequestAttribute::FILE_LAST_MODIFIED_TIME] ?? null;
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
