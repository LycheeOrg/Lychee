<?php

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasAbstractAlbum;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\FileStatus;
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

	protected ?int $file_last_modified_time;
	// protected UploadedFile $file;
	protected UploadedFile $file_chunk;
	protected UploadMetaResource $meta;
	protected int $file_size;

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
			'uuidName' => 'required|string|nullable',
			'extension' => 'required|string|nullable',
			'chunkNumber' => 'required|integer|min:1',
			'totalChunks' => 'required|integer',
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
		$this->file_last_modified_time = $val !== null ? intval($val) : null;
		$this->file_chunk = $files[RequestAttribute::FILE_ATTRIBUTE];
		$this->meta = new UploadMetaResource(
			file_name: $this->file_chunk->getClientOriginalName(),
			extension: $values['extension'] ?? null,
			uuid_name: $values['uuidName'] ?? null,
			stage: FileStatus::UPLOADING,
			chunk_number: $values['chunkNumber'],
			total_chunks: $values['totalChunks'],
		);
	}

	public function uploaded_file_chunk(): UploadedFile
	{
		return $this->file_chunk;
	}

	public function file_last_modified_time(): ?int
	{
		return $this->file_last_modified_time !== null ? intval($this->file_last_modified_time / 1000) : null;
	}

	public function meta(): UploadMetaResource
	{
		return $this->meta;
	}
}
