<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\DTO;

use App\Metadata\Extractor;
use App\Models\Album;

final class ImportParam
{
	/**
	 * @param ImportMode     $import_mode
	 * @param int            $intended_owner_id indicates the intended owner of the image
	 * @param Album|null     $album
	 * @param bool           $is_starred        indicates whether the new photo shall be starred
	 * @param Extractor|null $exif_info         the extracted EXIF information
	 *
	 * @return void
	 */
	public function __construct(
		public ImportMode $import_mode,
		public int $intended_owner_id,
		public Album|null $album = null,
		public bool $is_starred = false,
		public Extractor|null $exif_info = null,
	) {
	}
}
