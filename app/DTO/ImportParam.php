<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

use App\Enum\UserUploadTrustLevel;
use App\Exceptions\Internal\LycheeLogicException;
use App\Metadata\Extractor;
use App\Models\Album;

final class ImportParam
{
	public UserUploadTrustLevel $upload_trust_level;

	/**
	 * @param ImportMode     $import_mode
	 * @param int            $intended_owner_id indicates the intended owner of the image
	 * @param Album|null     $album
	 * @param bool           $is_highlighted    indicates whether the new photo shall be highlighted
	 * @param Extractor|null $exif_info         the extracted EXIF information
	 * @param bool|null      $apply_watermark   whether to apply watermark (null = use global setting)
	 * @param string|null    $title             user-supplied title override (takes precedence over EXIF-extracted title when non-null)
	 * @param string|null    $description       user-supplied description override (takes precedence over EXIF-extracted description when non-null)
	 * @param string|null    $preallocated_id   pre-allocated photo ID to be used on insert (see HasRandomIDAndLegacyTimeBasedID::preallocateId)
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
		public ?string $title = null,
		public ?string $description = null,
		public ?string $preallocated_id = null,
		?UserUploadTrustLevel $upload_trust_level = null,
	) {
		$this->upload_trust_level = $upload_trust_level ?? throw new LycheeLogicException('Upload trust level must be provided');
	}
}
