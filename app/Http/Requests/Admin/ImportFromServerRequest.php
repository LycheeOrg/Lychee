<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Admin;

use App\Contracts\Http\Requests\HasAbstractAlbum;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Exceptions\InvalidOptionsException;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAbstractAlbumTrait;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;

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
		return Gate::check(AlbumPolicy::CAN_IMPORT_FROM_SERVER, [AbstractAlbum::class]);
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

		if ($this->import_via_symlink && $this->delete_imported) {
			throw new InvalidOptionsException('The settings for import via symbolic links and deletion of imported files are conflicting');
		}
	}
}
