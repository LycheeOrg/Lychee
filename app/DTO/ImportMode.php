<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

/**
 * Define import mode.
 */
final readonly class ImportMode
{
	public readonly bool $shall_delete_imported;
	public readonly bool $shall_skip_duplicates;
	public readonly bool $shall_import_via_symlink;
	public readonly bool $shall_resync_metadata;
	public readonly bool $shall_rename_photo_title;
	public readonly bool $shall_rename_album_title;
	public readonly bool $shall_scope_to_owner;
	public readonly bool $is_guest_upload;

	public function __construct(
		bool $delete_imported = false,
		bool $skip_duplicates = false,
		bool $import_via_symlink = false,
		bool $resync_metadata = false,
		bool $shall_rename_photo_title = true,
		bool $shall_rename_album_title = true,
		bool $scope_to_owner = false,
		bool $guest_upload = false,
	) {
		$this->shall_delete_imported = $delete_imported;
		$this->shall_skip_duplicates = $skip_duplicates;
		// avoid incompatible settings (delete originals takes precedence over symbolic links)
		$this->shall_import_via_symlink = $delete_imported ? false : $import_via_symlink;
		// (re-syncing metadata makes no sense when importing duplicates)
		$this->shall_resync_metadata = !$skip_duplicates ? false : $resync_metadata;

		$this->shall_rename_photo_title = $shall_rename_photo_title;
		$this->shall_rename_album_title = $shall_rename_album_title;
		// Server imports only: another user's duplicate is rejected (FR-115-12).
		$this->shall_scope_to_owner = $scope_to_owner;
		// Guest uploads only: the guest owns no photo, so every duplicate is rejected (GHSA-9m66-3ccm-jfxg).
		$this->is_guest_upload = $guest_upload;
	}
}
