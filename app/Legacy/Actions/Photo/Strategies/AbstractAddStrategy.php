<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\Actions\Photo\Strategies;

use App\DTO\ImportParam;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Models\Photo;

abstract class AbstractAddStrategy
{
	protected function __construct(
		protected ImportParam $parameters,
		protected Photo $photo,
	) {
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
	 * {@link ImportParam::$exifInfo} attribute of the associated
	 * {@link ImportParam} object into the associated {@link Photo}
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
		if ($this->photo->title === null) {
			$this->photo->title = $this->parameters->exifInfo->title;
		}
		if ($this->photo->description === null) {
			$this->photo->description = $this->parameters->exifInfo->description;
		}
		if (count($this->photo->tags) === 0) {
			$this->photo->tags = $this->parameters->exifInfo->tags;
		}
		if ($this->photo->type === null) {
			$this->photo->type = $this->parameters->exifInfo->type;
		}
		if ($this->photo->iso === null) {
			$this->photo->iso = $this->parameters->exifInfo->iso;
		}
		if ($this->photo->aperture === null) {
			$this->photo->aperture = $this->parameters->exifInfo->aperture;
		}
		if ($this->photo->make === null) {
			$this->photo->make = $this->parameters->exifInfo->make;
		}
		if ($this->photo->model === null) {
			$this->photo->model = $this->parameters->exifInfo->model;
		}
		if ($this->photo->lens === null) {
			$this->photo->lens = $this->parameters->exifInfo->lens;
		}
		if ($this->photo->shutter === null) {
			$this->photo->shutter = $this->parameters->exifInfo->shutter;
		}
		if ($this->photo->focal === null) {
			$this->photo->focal = $this->parameters->exifInfo->focal;
		}
		if ($this->photo->taken_at === null) {
			$this->photo->taken_at = $this->parameters->exifInfo->taken_at;
			$this->photo->initial_taken_at = $this->parameters->exifInfo->taken_at;
		}
		if ($this->photo->latitude === null) {
			$this->photo->latitude = $this->parameters->exifInfo->latitude;
		}
		if ($this->photo->longitude === null) {
			$this->photo->longitude = $this->parameters->exifInfo->longitude;
		}
		if ($this->photo->altitude === null) {
			$this->photo->altitude = $this->parameters->exifInfo->altitude;
		}
		if ($this->photo->img_direction === null) {
			$this->photo->img_direction = $this->parameters->exifInfo->imgDirection;
		}
		if ($this->photo->location === null) {
			$this->photo->location = $this->parameters->exifInfo->location;
		}
		if ($this->photo->live_photo_content_id === null) {
			$this->photo->live_photo_content_id = $this->parameters->exifInfo->livePhotoContentID;
		}
	}

	/**
	 * @throws UnauthenticatedException
	 */
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
			$this->photo->owner_id = $this->parameters->intendedOwnerId;
		}
	}
}
