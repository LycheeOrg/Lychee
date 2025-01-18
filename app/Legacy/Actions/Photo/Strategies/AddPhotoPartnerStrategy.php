<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\Actions\Photo\Strategies;

use App\Contracts\Exceptions\LycheeException;
use App\DTO\ImportMode;
use App\DTO\ImportParam;
use App\Image\Files\NativeLocalFile;
use App\Models\Photo;

/**
 * Adds a photo as partner to an existing video.
 *
 * Note the asymmetry to {@link AddVideoPartnerStrategy}.
 * A photo is always added as if it had no partner, even if the video had
 * been added first.
 * Then the already existing video is added to the freshly added photo.
 * Hence, this strategy works mostly like the stand-alone strategy and also
 * requires the photo file to be a native, local file in order to be able to
 * extract EXIF data.
 */
final class AddPhotoPartnerStrategy extends AddStandaloneStrategy
{
	protected Photo $existingVideo;

	public function __construct(ImportParam $parameters, NativeLocalFile $photoSourceFile, Photo $existingVideo)
	{
		parent::__construct($parameters, $photoSourceFile);
		$this->existingVideo = $existingVideo;
	}

	/**
	 * @return Photo
	 *
	 * @throws LycheeException
	 */
	public function do(): Photo
	{
		// First add the source file as if it was a stand-alone photo
		// This creates and persists $this->photo as a new DB entry
		parent::do();

		// Now we re-use the same strategy as if the freshly created photo
		// entity had been uploaded first and as if the already existing video
		// had been uploaded after that.
		// We use the original size variant of the video as the "source file"
		// We request that the "imported" file shall be deleted, this actually
		// "steals away" the stored video file from the existing video entity
		// and moves it to the correct destination of a live partner for the
		// photo.
		$parameters = new ImportParam(
			new ImportMode(deleteImported: true),
			$this->parameters->intendedOwnerId
		);
		$videoStrategy = new AddVideoPartnerStrategy(
			$parameters,
			$this->existingVideo->size_variants->getOriginal()->getFile(),
			$this->photo
		);
		$videoStrategy->do();

		// If the video is uploaded already, we must copy over the checksum
		$this->photo->live_photo_checksum = $this->existingVideo->checksum;

		// Delete the existing video from whom we have stolen the video file
		// `delete()` also takes care of erasing all other size variants
		// from storage
		$this->existingVideo->delete();

		return $this->photo;
	}
}
