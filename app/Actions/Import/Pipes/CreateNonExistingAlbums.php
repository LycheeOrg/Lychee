<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Import\Pipes;

use App\Contracts\Import\ImportPipe;
use App\DTO\FolderNode;
use App\DTO\ImportDTO;
use App\DTO\ImportEventReport;
use App\DTO\ImportProgressReport;
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
		$this->report(ImportEventReport::createWarning('Create Albums', null, 'Creating non-existing albums...'));
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
		$this->report(ImportProgressReport::create('Processing folder: ' . $node->name, 0));

		// Check if an album with this title exists under the parent
		$album = $this->findOrCreateAlbum($node->name, $parent_album);
		$node->album = $album;

		// Process children first (bottom-up approach)
		foreach ($node->children as $child) {
			$this->processNode($child, $node->album);
		}

		// Import all images for this node
		// $this->importImagesForNode($node);
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
		// If we have a parent album, check if the child album already exists
		if ($parent_album !== null) {
			// Find albums with the given title under this parent
			$existing_album = Album::query()
				->join('base_albums', 'base_albums.id', '=', 'albums.id')
				->where('base_albums.title', $title)
				->where('albums.parent_id', $parent_album->id)
				->first();
		} else {
			// Check for root-level albums with this title
			$existing_album = Album::query()
				->join('base_albums', 'base_albums.id', '=', 'albums.id')
				->where('base_albums.title', $title)
				->whereNull('albums.parent_id')
				->first();

			if ($existing_album !== null) {
				$this->report(ImportEventReport::createWarning('album_exists', $title, 'Using existing album'));
				/** @var Album $album */
				$album = $existing_album;

				return $album;
			}
		}

		if ($existing_album !== null) {
			$this->report(ImportEventReport::createWarning('album_exists', $title, 'Using existing album'));
			/** @var Album $album */
			$album = $existing_album;

			return $album;
		}

		// Album doesn't exist, create it
		$album = $this->state->getAlbumCreate()->create($title, $parent_album);

		$this->report(ImportEventReport::createWarning('album_created', $title, 'Created new album'));

		return $album;
	}
}