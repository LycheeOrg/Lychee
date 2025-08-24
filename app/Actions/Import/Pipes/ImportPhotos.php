<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Import\Pipes;

use App\Constants\PhotoAlbum as PA;
use App\Contracts\Import\ImportPipe;
use App\DTO\FolderNode;
use App\DTO\ImportDTO;
use App\DTO\ImportEventReport;
use App\Image\Files\NativeLocalFile;
use App\Jobs\ImportImageJob;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;

class ImportPhotos implements ImportPipe
{
	use HasReporterTrait;

	protected ImportDTO $state;

	/**
	 * Import photos from the import state.
	 *
	 * @param ImportDTO                             $state
	 * @param \Closure(ImportDTO $state): ImportDTO $next
	 *
	 * @return ImportDTO
	 */
	public function handle(ImportDTO $state, \Closure $next): ImportDTO
	{
		$this->report(ImportEventReport::createNotice('Import Photos', null, 'Starting photo import...'));
		$this->state = $state;

		$this->processNode($state->root_folder);

		return $next($state);
	}

	private function processNode(FolderNode $node): void
	{
		// Logic to import photos for the given node
		$this->importImagesForNode($node);

		// Process each folder node to import photos
		foreach ($node->children as $child_node) {
			$this->processNode($child_node);
		}
	}

	/**
	 * Import all images associated with a node into its album.
	 *
	 * @param FolderNode $node Node containing images to import
	 *
	 * @return void
	 */
	private function importImagesForNode(FolderNode $node): void
	{
		$image_paths = $this->filterExistingPhotos($node->images, $node);

		$total = count($image_paths);
		if ($total === 0) {
			$this->report(ImportEventReport::createInfo('no_photos', $node->name, 'No new photos to import for this folder'));

			return;
		}

		foreach ($image_paths as $idx => $image_path) {
			$this->importSingleImage($image_path, $node->album, $idx / $total * 100);
		}
	}

	/**
	 * Filter the list of image_path with the non-already existing the database.
	 *
	 * @param array      $image_paths Filename to check
	 * @param FolderNode $node        Node to check
	 *
	 * @return array<int,string> list of photos that need to be imported
	 */
	private function filterExistingPhotos(array $image_paths, FolderNode $node): array
	{
		if ($node->album === null || !Configs::getValueAsBool('skip_duplicates_early')) {
			return $image_paths;
		}

		$this->report(ImportEventReport::createNotice('filtering', $node->name, 'Filtering photos.'));
		$already_existing = $this->findPhotoByFilenameInAlbum($image_paths, $node->album->id);

		foreach ($image_paths as $key => $image_path) {
			$basename = basename($image_path);
			$filename = pathinfo($image_path, PATHINFO_FILENAME);
			$renamed_basename = $this->state->getRenamer()->handle($basename);
			$renamed_filename = $this->state->getRenamer()->handle($filename);

			if (in_array($basename, $already_existing, true) ||
				in_array($filename, $already_existing, true) ||
				in_array($renamed_basename, $already_existing, true) ||
				in_array($renamed_filename, $already_existing, true)
			) {
				// If the photo already exists in the album, skip it
				$this->report(ImportEventReport::createWarning('skip_duplicate', basename($image_path), 'Skipped existing photo with same filename' . ($this->state->import_mode->shall_rename_photo_title ? ' (or renamed)' : '')));
				unset($image_paths[$key]);
			}
		}

		return array_values($image_paths);
	}

	/**
	 * Import a single image into an album.
	 *
	 * @param string     $image_path Path to the image file
	 * @param Album|null $album      Album to import into
	 *
	 * @return void
	 */
	private function importSingleImage(string $image_path, ?Album $album, int $progress): void
	{
		$file = new NativeLocalFile($image_path);
		$this->state->job_bus[] = new ImportImageJob($file, $this->state->intended_owner_id, $this->state->import_mode, $album);
		$this->report(ImportEventReport::createDebug('imported', $image_path, 'Created Import job for photo: ' . $progress . '%'));
	}

	/**
	 * Find a photo by filename within a specific album.
	 *
	 * @param array  $image_paths Filename to search for
	 * @param string $album_id    Album ID to search in
	 *
	 * @return array True if the photo exists
	 */
	private function findPhotoByFilenameInAlbum(array $image_paths, string $album_id): array
	{
		$base_names = array_map(fn ($i) => basename($i), $image_paths);
		$file_names = array_map(fn ($i) => pathinfo($i, PATHINFO_FILENAME), $image_paths);
		$candidates = array_merge($base_names, $file_names);

		if ($this->state->import_mode->shall_rename_photo_title) {
			$candidates_renamed = $this->state->getRenamer()->handleMany($candidates);
			$candidates = array_merge($candidates, $candidates_renamed);
		}

		return Photo::query()
			->join(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id')
			->where(PA::ALBUM_ID, $album_id)
			->whereIn('photos.title', $candidates)
			->pluck('photos.title')->all();
	}
}
