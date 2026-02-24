<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

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
use App\Rules\ExtensionRule;
use App\Rules\FileUuidRule;
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
	protected ?bool $apply_watermark = null;

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
			RequestAttribute::FILE_ATTRIBUTE => ['required', 'file'],
			'file_name' => 'required|string',
			'uuid_name' => ['present', new FileUuidRule()],
			'extension' => ['present', new ExtensionRule()],
			'chunk_number' => 'required|integer|min:1',
			'total_chunks' => 'required|integer|gte:chunk_number',
			'apply_watermark' => 'sometimes|boolean',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = $this->album_factory->findNullalbleAbstractAlbumOrFail($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]);
		// Convert the File Last Modified to seconds instead of milliseconds
		$val = $values[RequestAttribute::FILE_LAST_MODIFIED_TIME] ?? null;
		$this->file_last_modified_time = $val !== null ? intval($val) : null;
		$this->file_chunk = $files[RequestAttribute::FILE_ATTRIBUTE];
		$this->meta = new UploadMetaResource(
			file_name: $values['file_name'],
			extension: $values['extension'] ?? null,
			uuid_name: $values['uuid_name'] ?? null,
			stage: FileStatus::UPLOADING,
			chunk_number: $values['chunk_number'],
			total_chunks: $values['total_chunks'],
		);
		// Process apply_watermark parameter (optional boolean)
		if (isset($values['apply_watermark'])) {
			$this->apply_watermark = self::toBoolean($values['apply_watermark']);
		}
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

	public function apply_watermark(): ?bool
	{
		return $this->apply_watermark;
	}
}
