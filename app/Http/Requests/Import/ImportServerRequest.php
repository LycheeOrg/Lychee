<?php

namespace App\Http\Requests\Import;

use App\Actions\Photo\Strategies\ImportMode;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbum;
use App\Http\Requests\Contracts\RequestAttribute;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Models\Album;
use App\Models\Configs;
use App\Policies\UserPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;

class ImportServerRequest extends BaseApiRequest implements HasAlbum
{
	use HasAlbumTrait;

	public const PATH_ATTRIBUTE = 'paths';
	public const DELETE_IMPORTED_ATTRIBUTE = 'delete_imported';
	public const SKIP_DUPLICATES_ATTRIBUTE = 'skip_duplicates';
	public const IMPORT_VIA_SYMLINK_ATTRIBUTE = 'import_via_symlink';
	public const RESYNC_METADATA_ATTRIBUTE = 'resync_metadata';

	/** @var string[] */
	protected array $paths;

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
		return Gate::check(UserPolicy::IS_ADMIN);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
			self::PATH_ATTRIBUTE => 'required|array|min:1',
			self::PATH_ATTRIBUTE . '.*' => 'required|string|distinct',
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
		$albumID = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE];
		$this->album = $albumID === null ?
			null :
			Album::query()->findOrFail($albumID);
		$this->paths = $values[self::PATH_ATTRIBUTE];
		$this->importMode = new ImportMode(
			isset($values[self::DELETE_IMPORTED_ATTRIBUTE]) ?
				static::toBoolean($values[self::DELETE_IMPORTED_ATTRIBUTE]) :
				Configs::getValueAsBool('delete_imported'),
			isset($values[self::SKIP_DUPLICATES_ATTRIBUTE]) ?
				static::toBoolean($values[self::SKIP_DUPLICATES_ATTRIBUTE]) :
				Configs::getValueAsBool('skip_duplicates'),
			isset($values[self::IMPORT_VIA_SYMLINK_ATTRIBUTE]) ?
				static::toBoolean($values[self::IMPORT_VIA_SYMLINK_ATTRIBUTE]) :
				Configs::getValueAsBool('import_via_symlink'),
			isset($values[self::RESYNC_METADATA_ATTRIBUTE]) &&
				static::toBoolean($values[self::RESYNC_METADATA_ATTRIBUTE])
		);
	}

	/**
	 * @return string[]
	 */
	public function paths(): array
	{
		return $this->paths;
	}

	public function importMode(): ImportMode
	{
		return $this->importMode;
	}
}
