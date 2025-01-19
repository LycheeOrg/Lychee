<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Import;

use App\Contracts\Models\AbstractAlbum;
use App\DTO\ImportMode;
use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\HasAlbum;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\HasAlbumTrait;
use App\Legacy\V1\RuleSets\Import\ImportServerRuleSet;
use App\Models\Album;
use App\Models\Configs;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;

final class ImportServerRequest extends BaseApiRequest implements HasAlbum
{
	use HasAlbumTrait;

	/** @var string[] */
	protected array $paths;

	protected ImportMode $importMode;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_IMPORT_FROM_SERVER, AbstractAlbum::class);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return ImportServerRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var string|null */
		$albumID = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE];
		$this->album = $albumID === null ?
			null :
			// @codeCoverageIgnoreStart
			Album::query()->findOrFail($albumID);
		// @codeCoverageIgnoreEnd
		$this->paths = $values[RequestAttribute::PATH_ATTRIBUTE];
		$this->importMode = new ImportMode(
			isset($values[RequestAttribute::DELETE_IMPORTED_ATTRIBUTE]) ?
				static::toBoolean($values[RequestAttribute::DELETE_IMPORTED_ATTRIBUTE]) :
				Configs::getValueAsBool('delete_imported'),
			isset($values[RequestAttribute::SKIP_DUPLICATES_ATTRIBUTE]) ?
				static::toBoolean($values[RequestAttribute::SKIP_DUPLICATES_ATTRIBUTE]) :
				Configs::getValueAsBool('skip_duplicates'),
			isset($values[RequestAttribute::IMPORT_VIA_SYMLINK_ATTRIBUTE]) ?
				static::toBoolean($values[RequestAttribute::IMPORT_VIA_SYMLINK_ATTRIBUTE]) :
				Configs::getValueAsBool('import_via_symlink'),
			isset($values[RequestAttribute::RESYNC_METADATA_ATTRIBUTE]) &&
				static::toBoolean($values[RequestAttribute::RESYNC_METADATA_ATTRIBUTE])
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
