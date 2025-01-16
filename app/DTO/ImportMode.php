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
		bool $deleteImported = false,
		bool $skipDuplicates = false,
		bool $importViaSymlink = false,
		bool $resyncMetadata = false,
	) {
		$this->shallDeleteImported = $deleteImported;
		$this->shallSkipDuplicates = $skipDuplicates;
		// avoid incompatible settings (delete originals takes precedence over symbolic links)
		$this->shallImportViaSymlink = $deleteImported ? false : $importViaSymlink;
		// (re-syncing metadata makes no sense when importing duplicates)
		$this->shallResyncMetadata = !$skipDuplicates ? false : $resyncMetadata;
	}
}
