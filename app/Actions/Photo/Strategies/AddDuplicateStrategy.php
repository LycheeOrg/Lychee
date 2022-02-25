<?php

namespace App\Actions\Photo\Strategies;

use App\Exceptions\ModelDBException;
use App\Exceptions\PhotoSkippedException;
use App\Models\Logs;
use App\Models\Photo;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AddDuplicateStrategy extends AddBaseStrategy
{
	public function __construct(AddStrategyParameters $parameters, Photo $existing)
	{
		parent::__construct($parameters, $existing);
	}

	/**
	 * @throws PhotoSkippedException
	 * @throws ModelDBException
	 * @throws ModelNotFoundException
	 */
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
			throw new PhotoSkippedException();
		} else {
			// Duplicate the existing photo, this will also duplicate all
			// size variants without actually duplicating physical files
			$existing = $this->photo;
			$this->photo = $existing->replicate();
			// Adopt settings of duplicated photo acc. to target album
			$this->photo->is_public = $this->parameters->is_public;
			$this->photo->is_starred = $this->parameters->is_starred;
			$this->setParentAndOwnership();
			$this->photo->save();
		}

		return $this->photo;
	}
}
