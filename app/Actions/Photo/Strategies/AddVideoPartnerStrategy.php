<?php

namespace App\Actions\Photo\Strategies;

use App\Facades\Helpers;
use App\Models\Photo;

class AddVideoPartnerStrategy extends AddBaseStrategy
{
	public function __construct(AddStrategyParameters $parameters, Photo $existingPhoto)
	{
		parent::__construct($parameters, $existingPhoto);
	}

	public function do(): Photo
	{
		$photoPath = $this->photo->size_variants->getOriginal()->short_path;
		$photoExt = Helpers::getExtension($photoPath);
		$videoExt = $this->parameters->sourceFileInfo->getOriginalExtension();
		$videoPath = substr($photoPath, 0, -strlen($photoExt)) . $videoExt;
		$this->putSourceIntoFinalDestination($videoPath);
		$this->photo->live_photo_short_path = $videoPath;
		$this->photo->save();

		return $this->photo;
	}
}
