<?php

namespace App\Actions\Photo\Strategies;

use App\Exceptions\MediaFileOperationException;
use App\Exceptions\ModelDBException;
use App\Facades\AccessControl;
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

	/**
	 * @return Photo
	 *
	 * @throws ModelDBException
	 * @throws MediaFileOperationException
	 */
	abstract public function do(): Photo;

	/**
	 * Hydrates meta-info of the media file from the
	 * {@link AddStrategyParameters::$exifInfo} attribute of the associated
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
	protected function hydrateMetadata(): void
	{
		if (empty($this->photo->title) && !empty($this->parameters->exifInfo->title)) {
			$this->photo->title = $this->parameters->exifInfo->title;
		}
		if (empty($this->photo->description) && !empty($this->parameters->exifInfo->description)) {
			$this->photo->description = $this->parameters->exifInfo->description;
		}
		if (empty($this->photo->tags) && !empty($this->parameters->exifInfo->tags)) {
			$this->photo->tags = $this->parameters->exifInfo->tags;
		}
		if (empty($this->photo->type) && !empty($this->parameters->exifInfo->type)) {
			$this->photo->type = $this->parameters->exifInfo->type;
		}
		if (empty($this->photo->iso) && !empty($this->parameters->exifInfo->iso)) {
			$this->photo->iso = $this->parameters->exifInfo->iso;
		}
		if (empty($this->photo->aperture) && !empty($this->parameters->exifInfo->aperture)) {
			$this->photo->aperture = $this->parameters->exifInfo->aperture;
		}
		if (empty($this->photo->make) && !empty($this->parameters->exifInfo->make)) {
			$this->photo->make = $this->parameters->exifInfo->make;
		}
		if (empty($this->photo->model) && !empty($this->parameters->exifInfo->model)) {
			$this->photo->model = $this->parameters->exifInfo->model;
		}
		if (empty($this->photo->lens) && !empty($this->parameters->exifInfo->lens)) {
			$this->photo->lens = $this->parameters->exifInfo->lens;
		}
		if (empty($this->photo->shutter) && !empty($this->parameters->exifInfo->shutter)) {
			$this->photo->shutter = $this->parameters->exifInfo->shutter;
		}
		if (empty($this->photo->focal) && !empty($this->parameters->exifInfo->focal)) {
			$this->photo->focal = $this->parameters->exifInfo->focal;
		}
		if ($this->photo->taken_at === null && $this->parameters->exifInfo->taken_at !== null) {
			$this->photo->taken_at = $this->parameters->exifInfo->taken_at;
		}
		if ($this->photo->latitude === null && $this->parameters->exifInfo->latitude !== null) {
			$this->photo->latitude = $this->parameters->exifInfo->latitude;
		}
		if ($this->photo->longitude === null && $this->parameters->exifInfo->longitude !== null) {
			$this->photo->longitude = $this->parameters->exifInfo->longitude;
		}
		if ($this->photo->altitude === null && $this->parameters->exifInfo->altitude !== null) {
			$this->photo->altitude = $this->parameters->exifInfo->altitude;
		}
		if ($this->photo->img_direction === null && $this->parameters->exifInfo->imgDirection !== null) {
			$this->photo->img_direction = $this->parameters->exifInfo->imgDirection;
		}
		if (empty($this->photo->location) && !empty($this->parameters->exifInfo->location)) {
			$this->photo->location = $this->parameters->exifInfo->location;
		}
		if (empty($this->photo->live_photo_content_id) && !empty($this->parameters->exifInfo->livePhotoContentID)) {
			$this->photo->live_photo_content_id = $this->parameters->exifInfo->livePhotoContentID;
		}
	}

	protected function setParentAndOwnership(): void
	{
		if ($this->parameters->album !== null) {
			$this->photo->album_id = $this->parameters->album->id;
			// Avoid unnecessary DB request, when we access the album of a
			// photo later (e.g. when a notification is sent).
			$this->photo->setRelation('album', $this->parameters->album);
			$this->photo->owner_id = $this->parameters->album->owner_id;
		} else {
			$this->photo->album_id = null;
			// Avoid unnecessary DB request, when we access the album of a
			// photo later (e.g. when a notification is sent).
			$this->photo->setRelation('album', null);
			$this->photo->owner_id = AccessControl::id();
		}
	}
}
