<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\DTO;

/**
 * Define import mode.
 */
final readonly class ImportMode
{
	public bool $shallDeleteImported;
	public bool $shallSkipDuplicates;
	public bool $shallImportViaSymlink;
	public bool $shallResyncMetadata;

	public function __construct(
		bool $delete_imported = false,
		bool $skip_duplicates = false,
		bool $import_via_symlink = false,
		bool $resync_metadata = false,
	) {
		$this->shallDeleteImported = $delete_imported;
		$this->shallSkipDuplicates = $skip_duplicates;
		// avoid incompatible settings (delete originals takes precedence over symbolic links)
		$this->shallImportViaSymlink = $delete_imported ? false : $import_via_symlink;
		// (re-syncing metadata makes no sense when importing duplicates)
		$this->shallResyncMetadata = !$skip_duplicates ? false : $resync_metadata;
	}
}
