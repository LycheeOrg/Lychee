<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Admin;

use App\Contracts\Http\Requests\HasAbstractAlbum;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAbstractAlbumTrait;
use App\Models\Album;
use App\Models\Configs;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Auth;

class ImportFromServerRequest extends BaseApiRequest implements HasAbstractAlbum
{
	use HasAbstractAlbumTrait;
	/**
	 * The directories to import from.
	 *
	 * @var string[]
	 */
	public array $directories;
	public bool $delete_imported;
	public bool $import_via_symlink;
	public bool $skip_duplicates;
	public bool $resync_metadata;
	public bool $delete_missing_photos;
	public bool $delete_missing_albums;

	public function authorize(): bool
	{
		// Only the owner of Lychee can use this functionality
		return Auth::user() !== null && Auth::user()->id === Configs::getValueAsInt('owner_id');
	}

	/**
	 * Get the validation rules that apply to the request.
	 */
	public function rules(): array
	{
		return [
			'directories' => ['required', 'array', 'min:1'],
			'directories.*' => ['required', 'string'],
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
			'delete_imported' => ['required', 'boolean'],
			'import_via_symlink' => ['required', 'boolean'],
			'skip_duplicates' => ['required', 'boolean'],
			'resync_metadata' => ['required', 'boolean'],
			'delete_missing_photos' => ['required', 'boolean'],
			'delete_missing_albums' => ['required', 'boolean'],
		];
	}

	/**
	 * Configure the validator instance.
	 *
	 * @param \Illuminate\Validation\Validator $validator
	 *
	 * @return void
	 */
	public function withValidator($validator): void
	{
		$validator->after(function ($validator): void {
			// Check for conflicting settings
			if ($this->import_via_symlink && $this->delete_imported) {
				$validator->errors()->add(
					'import_via_symlink',
					'The settings for import via symbolic links and deletion of imported files are conflicting'
				);
			}
		});
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->directories = $values['directories'];

		// Set the album property
		$album_id = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE] ?? null;
		if ($album_id !== null) {
			$this->album = Album::query()->find($album_id);
		}

		$this->delete_imported = static::toBoolean($values['delete_imported']);
		$this->import_via_symlink = static::toBoolean($values['import_via_symlink']);
		$this->skip_duplicates = static::toBoolean($values['skip_duplicates']);
		$this->resync_metadata = static::toBoolean($values['resync_metadata']);
		$this->delete_missing_photos = static::toBoolean($values['delete_missing_photos']);
		$this->delete_missing_albums = static::toBoolean($values['delete_missing_albums']);
	}
}
