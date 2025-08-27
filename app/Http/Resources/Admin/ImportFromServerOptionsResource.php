<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Admin;

use App\Models\Configs;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class ImportFromServerOptionsResource extends Data
{
	public bool $delete_imported;
	public bool $import_via_symlink;
	public bool $skip_duplicates;
	public bool $resync_metadata;
	public bool $delete_missing_photos;
	public bool $delete_missing_albums;
	public string $directory;

	/**
	 * Creates a new resource instance.
	 * Initializes properties with values from the configuration.
	 */
	public function __construct()
	{
		$this->delete_imported = Configs::getValueAsBool('delete_imported');
		$this->import_via_symlink = Configs::getValueAsBool('import_via_symlink');
		$this->skip_duplicates = Configs::getValueAsBool('skip_duplicates');
		$this->resync_metadata = false; // Default value as this is not in config
		$this->delete_missing_photos = Configs::getValueAsBool('sync_delete_missing_photos');
		$this->delete_missing_albums = Configs::getValueAsBool('sync_delete_missing_albums');
		$this->directory = base_path('');
	}
}
