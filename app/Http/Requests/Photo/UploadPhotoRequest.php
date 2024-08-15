<?php

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasAbstractAlbum;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Http\Requests\Traits\HasAbstractAlbumTrait;
use App\Http\Resources\Editable\UploadMetaResource;
use App\Policies\AlbumPolicy;
use App\Rules\AlbumIDRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;

class UploadPhotoRequest extends BaseApiRequest implements HasAbstractAlbum
{
	use HasAbstractAlbumTrait;
	use AuthorizeCanEditAlbumTrait;

	protected ?int $fileLastModifiedTime;
	// protected UploadedFile $file;
	protected string $fileName;
	protected UploadedFile $fileChunk;
	protected UploadMetaResource $meta;
	protected int $lastModified;
	protected int $fileSize;
	protected int $progress;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, $this->album]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['present', new AlbumIDRule(true)],
			RequestAttribute::FILE_LAST_MODIFIED_TIME => 'sometimes|nullable|numeric',
			RequestAttribute::FILE_ATTRIBUTE => 'required|file',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = $this->albumFactory->findNullalbleAbstractAlbumOrFail($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]);
		// Convert the File Last Modified to seconds instead of milliseconds
		$val = $values[RequestAttribute::FILE_LAST_MODIFIED_TIME] ?? null;
		$this->fileLastModifiedTime = $val !== null ? intval($val) : null;
		$this->fileChunk = $files[RequestAttribute::FILE_ATTRIBUTE];
	}

	public function uploadedFileChunk(): UploadedFile
	{
		return $this->fileChunk;
	}

	public function fileLastModifiedTime(): ?int
	{
		return $this->fileLastModifiedTime !== null ? intval($this->fileLastModifiedTime / 1000) : null;
	}
}
