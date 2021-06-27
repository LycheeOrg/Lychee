<?php

namespace App\Actions\Photo\Strategies;

use App\Models\Photo;
use App\Models\SizeVariant;

class AddVideoPartnerStrategy extends AddBaseStrategy
{
	public function __construct(AddStrategyParameters $parameters, Photo $existingPhoto)
	{
		parent::__construct($parameters, $existingPhoto);
	}

	public function do(): Photo
	{
		$original = $this->photo->size_variants->getSizeVariant(SizeVariant::ORIGINAL);
		$ext = $this->parameters->sourceFileInfo->getOriginalFileExtension();
		$dstShortPath = pathinfo($original->short_path, PATHINFO_FILENAME) . $ext;
		$dstFullPath = pathinfo($original->full_path, PATHINFO_FILENAME) . $ext;
		$this->putSourceIntoFinalDestination($dstFullPath);
		$this->photo->live_photo_short_path = $dstShortPath;
		$this->photo->save();

		return $this->photo;
	}
}
