<?php

namespace App\Http\Requests\Import;

use App\Actions\Photo\Strategies\ImportMode;
use App\Facades\AccessControl;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAbstractAlbum;
use App\Http\Requests\Contracts\HasAlbum;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Models\Album;
use App\Models\Configs;
use App\Rules\RandomIDRule;

class ImportServerRequest extends BaseApiRequest implements HasAlbum
{
	use HasAlbumTrait;

	public const PATH_ATTRIBUTE = 'path';
	public const DELETE_IMPORTED_ATTRIBUTE = 'delete_imported';
	public const SKIP_DUPLICATES_ATTRIBUTE = 'skip_duplicates';
	public const IMPORT_VIA_SYMLINK_ATTRIBUTE = 'import_via_symlink';
	public const RESYNC_METADATA_ATTRIBUTE = 'resync_metadata';

	protected string $path;
	protected ImportMode $importMode;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		// This should always return true, because we already check that the
		// request is made by an admin during authentication (see
		// `routes/web.php`).
		// But better safe than sorry.
		return AccessControl::is_admin();
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAbstractAlbum::ALBUM_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
			self::PATH_ATTRIBUTE => 'required|string',
			self::DELETE_IMPORTED_ATTRIBUTE => 'sometimes|boolean',
			self::SKIP_DUPLICATES_ATTRIBUTE => 'sometimes|boolean',
			self::IMPORT_VIA_SYMLINK_ATTRIBUTE => 'sometimes|boolean',
			self::RESYNC_METADATA_ATTRIBUTE => 'sometimes|boolean',
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
			Album::query()->findOrFail($albumID);
		$this->path = $values[self::PATH_ATTRIBUTE];
		$this->importMode = new ImportMode(
			isset($values[self::DELETE_IMPORTED_ATTRIBUTE]) ?
				static::toBoolean($values[self::DELETE_IMPORTED_ATTRIBUTE]) :
				Configs::get_value('delete_imported', '0') === '1',
			isset($values[self::SKIP_DUPLICATES_ATTRIBUTE]) ?
				static::toBoolean($values[self::SKIP_DUPLICATES_ATTRIBUTE]) :
				Configs::get_value('skip_duplicates', '0') === '1',
			isset($values[self::IMPORT_VIA_SYMLINK_ATTRIBUTE]) ?
				static::toBoolean($values[self::IMPORT_VIA_SYMLINK_ATTRIBUTE]) :
				Configs::get_value('import_via_symlink', '0') === '1',
			isset($values[self::RESYNC_METADATA_ATTRIBUTE]) &&
				static::toBoolean($values[self::RESYNC_METADATA_ATTRIBUTE])
		);
	}

	public function path(): string
	{
		return $this->path;
	}

	public function importMode(): ImportMode
	{
		return $this->importMode;
	}
}
