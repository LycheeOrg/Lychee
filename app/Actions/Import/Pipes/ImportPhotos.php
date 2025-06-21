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
use App\Image\Files\NativeLocalFile;
use App\Models\Album;
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
		$this->report(ImportEventReport::createWarning('Import Photos', null, 'Starting photo import...'));
		$this->state = $state;

		$this->importPhotosForNode($state->root_folder);

		return $next($state);
	}

	private function importPhotosForNode(FolderNode $node): void
	{
		// Logic to import photos for the given node
		// This is a placeholder for actual photo import logic
		$this->report(ImportProgressReport::create('Importing photos for: ' . $node->name, 100));
		// Process each folder node to import photos
		foreach ($node->children as $child_node) {
			$this->importImagesForNode($child_node);
			$this->importPhotosForNode($child_node);
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
		foreach ($node->images as $image_path) {
			try {
				$this->importSingleImage($image_path, $node->album);
			} catch (\Throwable $e) {
				$this->report(ImportEventReport::createFromException($e, $image_path));
			}
		}
	}

	/**
	 * Import a single image into an album.
	 *
	 * @param string     $image_path Path to the image file
	 * @param Album|null $album      Album to import into
	 *
	 * @return void
	 */
	private function importSingleImage(string $image_path, ?Album $album): void
	{
		// First check if photo already exists in this album by filename
		$filename = basename($image_path);
		if ($this->photoExistsInAlbum($filename, $album)) {
			$this->report(ImportEventReport::createWarning('skip_duplicate', $image_path, 'Skipped existing photo'));

			return;
		}

		$file = new NativeLocalFile($image_path);
		$this->state->getPhotoCreate()->add($file, $album);

		$this->report(ImportEventReport::createWarning('imported', $image_path, 'Imported photo'));
	}

	/**
	 * Check if a photo with the given filename already exists in the album.
	 *
	 * @param string     $filename Filename to check
	 * @param Album|null $album    Album to check in
	 *
	 * @return bool True if the photo exists
	 */
	private function photoExistsInAlbum(string $filename, ?Album $album): bool
	{
		if ($album === null) {
			return false;
		}

		return $this->findPhotoByFilenameInAlbum($filename, $album->id);
	}

	/**
	 * Find a photo by filename within a specific album.
	 *
	 * @param string $filename Filename to search for
	 * @param string $album_id Album ID to search in
	 *
	 * @return bool True if the photo exists
	 */
	private function findPhotoByFilenameInAlbum(string $filename, string $album_id): bool
	{
		return Photo::query()
			->where('album_id', $album_id)
			->where('original_name', $filename)
			->exists();
	}
}
