<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Import\Pipes;

use App\Actions\Album\Delete;
use App\Contracts\Import\ImportPipe;
use App\DTO\FolderNode;
use App\DTO\ImportDTO;
use App\DTO\ImportEventReport;
use App\Models\Album;
use Illuminate\Support\Collection;

class DeleteMissingAlbums implements ImportPipe
{
	use HasReporterTrait;

	protected ImportDTO $state;

	/**
	 * Delete albums that exist in the database but not in the file system.
	 *
	 * @param ImportDTO                             $state
	 * @param \Closure(ImportDTO $state): ImportDTO $next
	 *
	 * @return ImportDTO
	 */
	public function handle(ImportDTO $state, \Closure $next): ImportDTO
	{
		if (!$state->delete_missing_albums) {
			return $next($state);
		}

		$this->report(ImportEventReport::createNotice('Delete Missing Albums', null, 'Starting to delete missing albums...'));
		$this->state = $state;

		$this->processNode($state->root_folder);

		return $next($state);
	}

	private function processNode(FolderNode $node): void
	{
		foreach ($node->children as $child_node) {
			$this->processNode($child_node);
		}

		// Process albums for this node
		$this->deleteMissingAlbums($node);
	}

	/**
	 * Handle the process of finding and possibly deleting missing albums.
	 *
	 * @param FolderNode $node Node containing album with subdirectories
	 *
	 * @return void
	 */
	private function deleteMissingAlbums(FolderNode $node): void
	{
		if ($node->album === null) {
			return;
		}

		$this->report(ImportEventReport::createDebug('checking_missing_albums', $node->name, 'Checking for missing albums'));

		// Find missing albums
		$albums_to_delete = $this->findMissingAlbums($node);

		$count = $albums_to_delete->count();
		if ($count === 0) {
			$this->report(ImportEventReport::createDebug('no_missing_albums', $node->name, 'No missing albums found'));

			return;
		}

		$this->report(ImportEventReport::createWarning('found_missing_albums', $node->name, "Found $count missing albums"));

		// Handle dry run mode
		if ($this->state->is_dry_run) {
			$this->handleDryRun($albums_to_delete, $node);

			return;
		}

		// Actually delete the albums
		$this->performDeletion($albums_to_delete, $node);
	}

	/**
	 * Find albums in the database that don't exist in the file system.
	 *
	 * @param FolderNode $node Node containing parent album and subdirectories
	 *
	 * @return Collection Albums to delete
	 */
	private function findMissingAlbums(FolderNode $node): Collection
	{
		// Get all directory names in the node
		$existing_folders = array_map(fn ($child) => $child->name, $node->children);

		// get renamed albums
		if ($this->state->import_mode->shall_rename_album_title) {
			$renamed_existing_folders = $this->state->getRenamer()->handleMany($existing_folders);
			$existing_folders = array_merge($existing_folders, $renamed_existing_folders);
		}

		// Find albums in the parent album that don't exist in the folder structure
		return Album::query()
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->select(['albums.id', 'base_albums.title'])
			->where('parent_id', $node->album->id)
			->whereNotIn('title', $existing_folders)
			->toBase()
			->get();
	}

	/**
	 * Handle dry run mode - report what would be deleted without making changes.
	 *
	 * @param Collection $albums_to_delete Albums that would be deleted
	 * @param FolderNode $node             Current folder node
	 *
	 * @return void
	 */
	private function handleDryRun(Collection $albums_to_delete, FolderNode $node): void
	{
		$count = $albums_to_delete->count();
		$this->report(ImportEventReport::createInfo('dry_run_albums', $node->name, "Dry run - would delete $count albums"));

		foreach ($albums_to_delete as $album) {
			$this->report(ImportEventReport::createDebug('dry_run_album', $node->name,
				sprintf('Would delete album %s (ID: %s)', $album->title, $album->id)));
		}
	}

	/**
	 * Actually delete the albums from the system.
	 *
	 * @param Collection $albums_to_delete Albums to delete
	 * @param FolderNode $node             Current folder node
	 *
	 * @return void
	 */
	private function performDeletion(Collection $albums_to_delete, FolderNode $node): void
	{
		$count = $albums_to_delete->count();
		foreach ($albums_to_delete as $album) {
			$this->report(ImportEventReport::createWarning('delete_album', $node->name,
				sprintf('Deleting album %s (ID: %s)', $album->title, $album->id)));
		}

		// Execute the deletion
		$delete = new Delete();
		$file_deleter = $delete->do($albums_to_delete->pluck('id')->all());
		$file_deleter->do();

		$this->report(ImportEventReport::createError('deleted_missing_albums', $node->name, "Deleted $count albums"));
	}
}
