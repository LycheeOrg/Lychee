<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Admin;

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
		$this->delete_imported = request()->configs()->getValueAsBool('delete_imported');
		$this->import_via_symlink = request()->configs()->getValueAsBool('import_via_symlink');
		$this->skip_duplicates = request()->configs()->getValueAsBool('skip_duplicates');
		$this->resync_metadata = false; // Default value as this is not in config
		$this->delete_missing_photos = request()->configs()->getValueAsBool('sync_delete_missing_photos');
		$this->delete_missing_albums = request()->configs()->getValueAsBool('sync_delete_missing_albums');
		$this->directory = base_path('');
	}
}
