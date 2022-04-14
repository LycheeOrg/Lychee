<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAbstractAlbum;
use App\Http\Requests\Traits\HasAbstractAlbumTrait;
use App\Rules\AlbumIDRule;
use Illuminate\Http\UploadedFile;

class AddPhotoRequest extends BaseApiRequest implements HasAbstractAlbum
{
	use HasAbstractAlbumTrait;

	public const FILE_ATTRIBUTE = 'file';
	protected UploadedFile $file;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumWrite($this->album);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAbstractAlbum::ALBUM_ID_ATTRIBUTE => ['present', new AlbumIDRule(true)],
			self::FILE_ATTRIBUTE => 'required|file',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$albumID = $values[HasAbstractAlbum::ALBUM_ID_ATTRIBUTE];
		$this->album = empty($albumID) ?
			null :
			$this->albumFactory->findAbstractAlbumOrFail($albumID);
		if (empty($this->albumID)) {
			$this->albumID = null;
		}
		$this->file = $files[self::FILE_ATTRIBUTE];
	}

	public function uploadedFile(): UploadedFile
	{
		return $this->file;
	}
}
