<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Import\Pipes;

use App\Contracts\Import\ImportPipe;
use App\DTO\FolderNode;
use App\DTO\ImportDTO;
use App\DTO\ImportEventReport;
use App\Models\Album;

class CreateNonExistingAlbums implements ImportPipe
{
	use HasReporterTrait;

	protected ImportDTO $state;

	/**
	 * Create non-existing albums in the import state.
	 *
	 * @param ImportDTO                             $state
	 * @param \Closure(ImportDTO $state): ImportDTO $next
	 *
	 * @return ImportDTO
	 */
	public function handle(ImportDTO $state, \Closure $next): ImportDTO
	{
		$this->report(ImportEventReport::createNotice('Create Albums', null, 'Creating non-existing albums...'));
		$this->state = $state;

		$this->processNode($state->root_folder, $state->parent_album);

		return $next($state);
	}

	/**
	 * Create albums and import photos starting from the bottom of the tree.
	 *
	 * @param FolderNode $node         Current node to process
	 * @param Album|null $parent_album Parent album (for nesting)
	 *
	 * @return void
	 */
	private function processNode(FolderNode $node, ?Album $parent_album = null): void
	{
		$this->report(ImportEventReport::createDebug('Processing folder', $node->name, 'Processing folder'));

		// Check if an album with this title exists under the parent
		$album = $this->findOrCreateAlbum($node->name, $parent_album);
		$node->album = $album;

		// Process children first (bottom-up approach)
		foreach ($node->children as $child) {
			$this->processNode($child, $node->album);
		}
	}

	/**
	 * Find an album by title under a parent album or create it if it doesn't exist.
	 *
	 * @param string     $title        Album title
	 * @param Album|null $parent_album Parent album
	 *
	 * @return Album The found or created album
	 */
	private function findOrCreateAlbum(string $title, ?Album $parent_album): Album
	{
		$existing_album = null;

		$old_title = $title;

		// We do this to keep backward compatibility with previously imported albums.
		if ($this->state->import_mode->shall_rename_album_title) {
			$renamer = $this->state->getAlbumRenamer();
			$title = $renamer->handle($title);
		}

		// If we have a parent album, check if the child album already exists
		if ($parent_album !== null) {
			// Find albums with the given title under this parent
			/** @var Album|null $existing_album */
			$existing_album = Album::query()
				->join('base_albums', 'base_albums.id', '=', 'albums.id')
				->where(fn ($q) => $q->where('base_albums.title', $title)
					->orWhere('base_albums.title', $old_title))
				->where('albums.parent_id', $parent_album->id)
				->first();
		} else {
			// Check for root-level albums with this title
			/** @var Album|null $existing_album */
			$existing_album = Album::query()
				->join('base_albums', 'base_albums.id', '=', 'albums.id')
				->where(fn ($q) => $q->where('base_albums.title', $title)
					->orWhere('base_albums.title', $old_title))
				->whereNull('albums.parent_id')
				->first();
		}

		if ($existing_album !== null) {
			$this->report(ImportEventReport::createDebug('album_exists', $existing_album->title, 'Using existing album'));
			/** @var Album $album */
			$album = $existing_album;

			return $album;
		}

		// Album doesn't exist, create it
		$album = $this->state->getAlbumCreate()->create($title, $parent_album);

		$this->report(ImportEventReport::createInfo('album_created', $title, 'Created new album'));

		return $album;
	}
}