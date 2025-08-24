<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\DTO;

use App\Actions\Album\Create as AlbumCreate;
use App\Actions\Photo\Create as PhotoCreate;
use App\Jobs\ImportImageJob;
use App\Metadata\Renamer;
use App\Models\Album;

class ImportDTO
{
	protected AlbumCreate $album_create;
	protected PhotoCreate $photo_create;
	public FolderNode $root_folder;
	protected Renamer $renamer;

	/** @var ImportImageJob[] $job_bus */
	public array $job_bus = [];

	public function __construct(
		public readonly int $intended_owner_id,
		public readonly ImportMode $import_mode,
		public readonly ?Album $parent_album,
		public readonly string $path,
		public readonly bool $delete_missing_photos = false,
		public readonly bool $delete_missing_albums = false,
		public readonly bool $is_dry_run = true,
		public readonly bool $should_execute_bath = false,
	) {
		$this->album_create = new AlbumCreate($intended_owner_id);
		$this->photo_create = new PhotoCreate($import_mode, $intended_owner_id);
		$this->renamer = new Renamer($intended_owner_id);
	}

	public function getAlbumCreate(): AlbumCreate
	{
		return $this->album_create;
	}

	public function getPhotoCreate(): PhotoCreate
	{
		return $this->photo_create;
	}

	public function getRenamer(): Renamer
	{
		return $this->renamer;
	}
}