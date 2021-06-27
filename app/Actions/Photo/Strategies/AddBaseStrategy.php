<?php

namespace App\Actions\Photo\Strategies;

use App\Exceptions\JsonError;
use App\Facades\AccessControl;
use App\Models\Logs;
use App\Models\Photo;

abstract class AddBaseStrategy
{
	protected AddStrategyParameters $parameters;
	protected Photo $photo;

	protected function __construct(AddStrategyParameters $parameters, Photo $photo)
	{
		$this->parameters = $parameters;
		$this->photo = $photo;
	}

	abstract public function do(): Photo;

	/**
	 * Hydrates meta-info of the media file from the
	 * {@link AddStrategyParameters::$info} attribute of the associated
	 * {@link AddStrategyParameters} object into the associated {@link Photo}
	 * object.
	 *
	 * Meta information is conditionally copied if and only if the target
	 * attribute of the {@link Photo} object is null or empty and the
	 * meta-info is not.
	 * This way this method is usable by {@link AddStandaloneStrategy} and
	 * {@link AddDuplicateStrategy}.
	 * For a freshly created {@link Photo} object (with empty attributes)
	 * all available meta-data is hydrated, but for an already existing
	 * {@link Photo} object existing attributes are not overwritten.
	 */
	protected function hydrateMetadata()
	{
		if (empty($this->photo->title) && !empty($this->parameters->info['title'])) {
			$this->photo->title = $this->parameters->info['title'];
		}
		if (empty($this->photo->description) && !empty($this->parameters->info['description'])) {
			$this->photo->description = $this->parameters->info['description'];
		}
		if (empty($this->photo->tags) && !empty($this->parameters->info['tags'])) {
			$this->photo->tags = $this->parameters->info['tags'];
		}
		if (empty($this->photo->type) && !empty($this->parameters->info['type'])) {
			$this->photo->type = $this->parameters->info['type'];
		}
		$tmp = empty($this->parameters->info['filesize']) ? 0 : intval($this->parameters->info['filesize']);
		if ($tmp > 0) {
			$this->photo->filesize = $tmp;
		}
		if (empty($this->photo->checksum) && !empty($this->parameters->info['checksum'])) {
			$this->photo->checksum = $this->parameters->info['checksum'];
		}
		if (empty($this->photo->iso) && !empty($this->parameters->info['iso'])) {
			$this->photo->iso = $this->parameters->info['iso'];
		}
		if (empty($this->photo->aperture) && !empty($this->parameters->info['aperture'])) {
			$this->photo->aperture = $this->parameters->info['aperture'];
		}
		if (empty($this->photo->make) && !empty($this->parameters->info['make'])) {
			$this->photo->make = $this->parameters->info['make'];
		}
		if (empty($this->photo->model) && !empty($this->parameters->info['model'])) {
			$this->photo->model = $this->parameters->info['model'];
		}
		if (empty($this->photo->lens) && !empty($this->parameters->info['lens'])) {
			$this->photo->lens = $this->parameters->info['lens'];
		}
		if (empty($this->photo->shutter) && !empty($this->parameters->info['shutter'])) {
			$this->photo->shutter = $this->parameters->info['shutter'];
		}
		if (empty($this->photo->focal) && !empty($this->parameters->info['focal'])) {
			$this->photo->focal = $this->parameters->info['focal'];
		}
		if ($this->photo->taken_at === null && !empty($this->parameters->info['taken_at'])) {
			$this->photo->taken_at = $this->parameters->info['taken_at'];
		}
		if ($this->photo->latitude === null && !empty($this->parameters->info['latitude'])) {
			$this->photo->latitude = floatval($this->parameters->info['latitude']);
		}
		if ($this->photo->longitude === null && !empty($this->parameters->info['longitude'])) {
			$this->photo->longitude = floatval($this->parameters->info['longitude']);
		}
		if ($this->photo->altitude === null && !empty($this->parameters->info['altitude'])) {
			$this->photo->altitude = floatval($this->parameters->info['altitude']);
		}
		if ($this->photo->imgDirection === null && !empty($this->parameters->info['imgDirection'])) {
			$this->photo->imgDirection = floatval($this->parameters->info['imgDirection']);
		}
		if (empty($this->photo->location) && !empty($this->parameters->info['location'])) {
			$this->photo->location = $this->parameters->info['location'];
		}
		if (empty($this->photo->live_photo_content_id) && !empty($this->parameters->info['live_photo_content_id'])) {
			$this->photo->live_photo_content_id = $this->parameters->info['live_photo_content_id'];
		}
	}

	protected function setParentAndOwnership()
	{
		if ($this->parameters->album !== null) {
			$this->photo->album_id = $this->parameters->album->id;
			$this->photo->owner_id = $this->parameters->album->owner_id;
		} else {
			$this->photo->album_id = null;
			$this->photo->owner_id = AccessControl::id();
		}
	}

	/**
	 * Moves/copies/symlinks source file to final destination.
	 *
	 * @param string $targetFullPath the path of the final destination
	 *
	 * @throws JsonError
	 */
	protected function putSourceIntoFinalDestination(string $targetFullPath): void
	{
		$tmpFullPath = $this->parameters->sourceFileInfo->getTmpFullPath();
		if ($this->parameters->importMode->shallDeleteImported()) {
			// Note: We cannot use the storage facade here, because the
			// temporary name of the original file may be outside of the
			// disk of the storage
			if (!rename($tmpFullPath, $targetFullPath)) {
				$msg = 'Could not move photo from "' . $tmpFullPath . '" to "' . $targetFullPath . '"';
				Logs::error(__METHOD__, __LINE__, $msg);
				throw new JsonError('$msg');
			}
		} else {
			// Check if the user wants to create symlinks instead of copying the photo
			if ($this->parameters->importMode->shallImportViaSymlink()) {
				if (!symlink($tmpFullPath, $targetFullPath)) {
					$msg = 'Could not create symbolic link at "' . $targetFullPath . '" for photo at "' . $tmpFullPath . '"';
					Logs::error(__METHOD__, __LINE__, $msg);
					throw new JsonError($msg);
				}
			} elseif (!copy($tmpFullPath, $targetFullPath)) {
				$msg = 'Could not copy photo from "' . $tmpFullPath . '" to "' . $targetFullPath . '"';
				Logs::error(__METHOD__, __LINE__, $msg);
				throw new JsonError($msg);
			}
		}

		// Set original date
		if ($this->photo->taken_at !== null) {
			@touch($targetFullPath, $this->photo->taken_at->getTimestamp());
		}
	}
}
