<?php

namespace App\Actions\Photo\Strategies;

use App\Exceptions\PhotoSkippedException;
use App\Models\Logs;
use App\Models\Photo;

class AddDuplicateStrategy extends AddBaseStrategy
{
	public function __construct(AddStrategyParameters $parameters, Photo $existing)
	{
		parent::__construct($parameters, $existing);
	}

	public function do(): Photo
	{
		// At least update the existing photo with additional metadata if
		// available
		$this->hydrateMetadata();
		if ($this->photo->isDirty()) {
			Logs::notice(__METHOD__, __LINE__, 'Updating metadata of existing photo.');
			$this->photo->save();
		}

		if ($this->parameters->importMode->shallSkipDuplicates()) {
			Logs::notice(__METHOD__, __LINE__, 'Skipped upload of existing photo because skipDuplicates is activated');
			// TODO: Think again of this. A "usual" case should not result in an exception.
			throw new PhotoSkippedException('This photo has been skipped because it\'s already in your library.');
		} else {
			// Duplicate the existing photo, this will also duplicate all
			// size variants without actually duplicating physical files
			$existing = $this->photo;
			$this->photo = $existing->replicate();
			// Adopt settings of duplicated photo acc. to target album
			$this->photo->public = $this->parameters->public;
			$this->photo->star = $this->parameters->star;
			$this->setParentAndOwnership();
			$this->photo->save();
		}

		return $this->photo;
	}
}
