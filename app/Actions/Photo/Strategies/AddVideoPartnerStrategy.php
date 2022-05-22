<?php

namespace App\Actions\Photo\Strategies;

use App\Exceptions\MediaFileOperationException;
use App\Exceptions\ModelDBException;
use App\Image\MediaFile;
use App\Models\Photo;

/**
 * Adds a video as partner to an existing photo.
 *
 * Note the asymmetry to {@link AddPhotoPartnerStrategy}.
 * A video is always added to an already existing photo, and, in particular,
 * all EXIF data are taken from the that photo.
 * This allows to use {@link MediaFile} as the source of the video, because
 * no EXIF data needs to be extracted from the video.
 */
class AddVideoPartnerStrategy extends AddBaseStrategy
{
	protected MediaFile $videoSourceFile;

	public function __construct(AddStrategyParameters $parameters, MediaFile $videoSourceFile, Photo $existingPhoto)
	{
		parent::__construct($parameters, $existingPhoto);
		$this->videoSourceFile = $videoSourceFile;
	}

	/**
	 * @return Photo
	 *
	 * @throws MediaFileOperationException
	 * @throws ModelDBException
	 */
	public function do(): Photo
	{
		$photoFile = $this->photo->size_variants->getOriginal()->getFile();
		$photoPath = $photoFile->getRelativePath();
		$photoExt = $photoFile->getOriginalExtension();
		$videoExt = $this->videoSourceFile->getOriginalExtension();
		$videoPath = substr($photoPath, 0, -strlen($photoExt)) . $videoExt;
		$this->putSourceIntoFinalDestination($this->videoSourceFile, $videoPath);
		$this->photo->live_photo_short_path = $videoPath;
		$this->photo->save();

		return $this->photo;
	}
}
