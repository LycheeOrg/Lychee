<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
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
	 * @param bool           $is_highlighted    indicates whether the new photo shall be highlighted
	 * @param Extractor|null $exif_info         the extracted EXIF information
	 * @param bool|null      $apply_watermark   whether to apply watermark (null = use global setting)
	 *
	 * @return void
	 */
	public function __construct(
		public ImportMode $import_mode,
		public int $intended_owner_id,
		public Album|null $album = null,
		public bool $is_highlighted = false,
		public Extractor|null $exif_info = null,
		public ?bool $apply_watermark = null,
	) {
	}
}
