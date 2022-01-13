<?php

namespace App\Actions\Photo\Strategies;

use App\Models\Photo;

class AddVideoPartnerStrategy extends AddBaseStrategy
{
	public function __construct(AddStrategyParameters $parameters, Photo $existingPhoto)
	{
		parent::__construct($parameters, $existingPhoto);
	}

	public function do(): Photo
	{
		$original = $this->photo->size_variants->getOriginal();
		$ext = $this->parameters->sourceFileInfo->getOriginalFileExtension();
		$dstShortPath = substr($original->short_path, 0, -strlen($ext)) . $ext;
		$dstFullPath = substr($original->full_path, 0, -strlen($ext)) . $ext;
		$this->putSourceIntoFinalDestination($dstFullPath);
		$this->photo->live_photo_short_path = $dstShortPath;
		$this->photo->save();

		return $this->photo;
	}
}
