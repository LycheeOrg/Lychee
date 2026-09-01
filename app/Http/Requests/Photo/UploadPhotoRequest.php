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
use App\Rules\ChunkSequenceRule;
use App\Rules\DescriptionRule;
use App\Rules\ExtensionRule;
use App\Rules\FilenameRule;
use App\Rules\FileUuidRule;
use App\Rules\TitleRule;
use Illuminate\Contracts\Validation\Validator;
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
	protected ?string $title = null;
	protected ?string $description = null;
	private ?ChunkSequenceRule $chunk_sequence_rule = null;

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
			'file_name' => ['required', new FilenameRule()],
			'uuid_name' => ['present', new FileUuidRule(), $this->chunkSequenceRule()],
			'extension' => ['present', new ExtensionRule()],
			'chunk_number' => 'required|integer|min:1',
			'total_chunks' => 'required|integer|gte:chunk_number',
			'apply_watermark' => 'sometimes|boolean',
			RequestAttribute::TITLE_ATTRIBUTE => ['sometimes', 'nullable', new TitleRule()],
			RequestAttribute::DESCRIPTION_ATTRIBUTE => ['sometimes', 'nullable', new DescriptionRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 *
	 * If some other field fails validation after ChunkSequenceRule already
	 * acquired its mutex, release it immediately instead of leaving it held
	 * for its full TTL while the controller never runs.
	 */
	public function withValidator(Validator $validator): void
	{
		$validator->after(function (Validator $validator): void {
			if ($validator->errors()->isNotEmpty()) {
				$this->chunkSequenceRule()->releaseWithoutCommit();
			}
		});
	}

	private function chunkSequenceRule(): ChunkSequenceRule
	{
		return $this->chunk_sequence_rule ??= new ChunkSequenceRule();
	}

	/**
	 * Records that $chunk_number has been durably appended to the staging
	 * file, releasing the mutex acquired by ChunkSequenceRule during
	 * validation. Must be called exactly once, immediately after the
	 * controller has appended the chunk's bytes to the staging file.
	 */
	public function completeChunkUpload(string $uuid_name, int $chunk_number, bool $is_last_chunk): void
	{
		$this->chunkSequenceRule()->completeAppend($uuid_name, $chunk_number, $is_last_chunk);
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
		// Store optional user-supplied title and description
		$this->title = $values[RequestAttribute::TITLE_ATTRIBUTE] ?? null;
		$this->description = $values[RequestAttribute::DESCRIPTION_ATTRIBUTE] ?? null;
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

	public function title(): ?string
	{
		return $this->title;
	}

	public function description(): ?string
	{
		return $this->description;
	}
}
