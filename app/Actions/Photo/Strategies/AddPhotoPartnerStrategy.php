<?php

namespace App\Actions\Photo\Strategies;

use App\Actions\Photo\Extensions\SourceFileInfo;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;
use App\Exceptions\ModelDBException;
use App\Models\Photo;

class AddPhotoPartnerStrategy extends AddStandaloneStrategy
{
	protected Photo $existingVideo;

	public function __construct(AddStrategyParameters $parameters, Photo $existingVideo)
	{
		parent::__construct($parameters);
		$this->existingVideo = $existingVideo;
	}

	/**
	 * @return Photo
	 *
	 * @throws ModelDBException
	 * @throws MediaFileOperationException
	 * @throws QueryBuilderException
	 * @throws MediaFileUnsupportedException
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
		$parameters = new AddStrategyParameters(new ImportMode(true));
		$parameters->sourceFileInfo = SourceFileInfo::createByPhoto($this->existingVideo);
		$videoStrategy = new AddVideoPartnerStrategy($parameters, $this->photo);
		$videoStrategy->do();

		// Delete the existing video from whom we have stolen the video file
		// `delete()` also takes care of erasing all other size variants
		// from storage
		$this->existingVideo->delete();

		return $this->photo;
	}
}
