<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Import\Pipes;

use App\Actions\Photo\Delete;
use App\Constants\PhotoAlbum as PA;
use App\Contracts\Import\ImportPipe;
use App\DTO\FolderNode;
use App\DTO\ImportDTO;
use App\DTO\ImportEventReport;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Collection;

class DeleteMissingPhotos implements ImportPipe
{
	use HasReporterTrait;

	protected ImportDTO $state;

	/**
	 * Delete photos that exist in the database but not in the file system.
	 *
	 * @param ImportDTO                             $state
	 * @param \Closure(ImportDTO $state): ImportDTO $next
	 *
	 * @return ImportDTO
	 */
	public function handle(ImportDTO $state, \Closure $next): ImportDTO
	{
		if (!$state->delete_missing_photos) {
			return $next($state);
		}

		$this->report(ImportEventReport::createNotice('Delete Missing Photos', null, 'Starting to delete missing photos...'));
		$this->state = $state;

		$this->processNode($state->root_folder);

		return $next($state);
	}

	private function processNode(FolderNode $node): void
	{
		// Skip if no album is associated with this node
		if ($node->album === null) {
			return;
		}

		// Process photos for this node
		$this->deleteMissingPhotos($node);

		// Process each child node
		foreach ($node->children as $child_node) {
			$this->processNode($child_node);
		}
	}

	/**
	 * Handle the process of finding and possibly deleting missing photos.
	 *
	 * @param FolderNode $node Node containing album with photos
	 *
	 * @return void
	 */
	private function deleteMissingPhotos(FolderNode $node): void
	{
		if ($node->album === null) {
			return;
		}

		$this->report(ImportEventReport::createDebug('checking_missing', $node->name, 'Checking for missing photos'));

		// Find missing photos
		$photos_to_delete = $this->findMissingPhotos($node);

		$count = $photos_to_delete->count();
		if ($count === 0) {
			$this->report(ImportEventReport::createDebug('no_missing_photos', $node->name, 'No missing photos found'));

			return;
		}

		$this->report(ImportEventReport::createWarning('found_missing', $node->name, "Found $count missing photos"));

		// Handle dry run mode
		if ($this->state->is_dry_run) {
			$this->handleDryRun($photos_to_delete, $node);

			return;
		}

		// Actually delete the photos
		$this->performDeletion($photos_to_delete, $node);
	}

	/**
	 * Find photos in the database that don't exist in the file system.
	 *
	 * @param FolderNode $node Node containing album and images
	 *
	 * @return Collection<int,Photo> Photos to delete
	 */
	private function findMissingPhotos(FolderNode $node): Collection
	{
		// Get all photo filenames in the directory
		$existing_filenames = array_map(fn ($path) => basename($path), $node->images);
		$existing_filenames_no_ext = array_map(fn ($path) => pathinfo($path, PATHINFO_FILENAME), $node->images);
		$existing_files = array_merge($existing_filenames, $existing_filenames_no_ext);

		// Find photos in the album that don't exist in the folder
		return Photo::query()
			->select(['photos.id', 'photos.title'])
			->join(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id')
			->where(PA::ALBUM_ID, $node->album->id)
			->whereNotIn('photos.title', $existing_files)
			->get();
	}

	/**
	 * Handle dry run mode - report what would be deleted without making changes.
	 *
	 * @param Collection<int,Photo> $photos_to_delete Photos that would be deleted
	 * @param FolderNode            $node             Current folder node
	 *
	 * @return void
	 */
	private function handleDryRun(Collection $photos_to_delete, FolderNode $node): void
	{
		$count = $photos_to_delete->count();
		$this->report(ImportEventReport::createInfo('dry_run', $node->name, "Dry run - would delete $count photos"));

		foreach ($photos_to_delete as $photo) {
			$this->report(ImportEventReport::createDebug('dry_run_photo', $node->name,
				sprintf('Would delete %s (ID: %s)', $photo->title, $photo->id)));
		}
	}

	/**
	 * Actually delete the photos from the system.
	 *
	 * @param Collection<int,Photo> $photos_to_delete Photos to delete
	 * @param FolderNode            $node             Current folder node
	 *
	 * @return void
	 */
	private function performDeletion(Collection $photos_to_delete, FolderNode $node): void
	{
		$count = $photos_to_delete->count();

		foreach ($photos_to_delete as $photo) {
			$this->report(ImportEventReport::createWarning('delete_photo', $node->name,
				sprintf('Deleting %s (ID: %s)', $photo->title, $photo->id)));
		}

		// Execute the deletion
		$delete = new Delete();
		$file_deleter = $delete->do($photos_to_delete->pluck('id')->all(), $node->album->id);
		$file_deleter->do();

		$this->report(ImportEventReport::createError('deleted_missing', $node->name, "Deleted $count missing photos"));
	}
}
