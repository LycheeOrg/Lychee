<?php

namespace App\Actions\Photo\Strategies;

use App\Actions\Photo\Create;
use App\Actions\Photo\Extensions\Metadata;
use App\Exceptions\PhotoResyncedException;
use App\Exceptions\PhotoSkippedException;
use App\Models\Logs;
use App\Models\Photo;
use Storage;

class StrategyDuplicate extends StrategyPhotoBase
{
	public $skip_duplicates;
	public $resync_metadata;
	public $delete_imported;

	public function __construct(
		bool $skip_duplicates,
		bool $resync_metadata,
		bool $delete_imported
	) {
		$this->skip_duplicates = $skip_duplicates;
		$this->resync_metadata = $resync_metadata;
		$this->delete_imported = $delete_imported;
	}

	public function storeFile(Create $create)
	{
		Logs::notice(__METHOD__, __LINE__, 'Nothing to store, image is a duplicate');
	}

	public function hydrate(Create &$create, ?Photo &$existing = null, ?array $file = null)
	{
		$create->photo_Url = $existing->url;
		$create->path = Storage::path($create->path_prefix . $existing->url);
		$create->photo->thumbUrl = $existing->thumbUrl;
		$create->photo->thumb2x = $existing->thumb2x;
		$create->photo->medium_width = $existing->medium_width;
		$create->photo->medium_height = $existing->medium_height;
		$create->photo->medium2x_width = $existing->medium2x_width;
		$create->photo->medium2x_height = $existing->medium2x_height;
		$create->photo->small_width = $existing->small_width;
		$create->photo->small_height = $existing->small_height;
		$create->photo->small2x_width = $existing->small2x_width;
		$create->photo->small2x_height = $existing->small2x_height;
		$create->photo->livePhotoUrl = $existing->livePhotoUrl;
		$create->photo->livePhotoChecksum = $existing->livePhotoChecksum;
		$create->photo->checksum = $existing->checksum;
		$create->photo->type = $existing->type;
		$create->mimeType = $create->photo->type;

		// Photo already exists
		// Check if the user wants to skip duplicates
		if ($this->skip_duplicates) {
			$metadataChanged = false;

			// Before we skip entirely, check if there is a sidecar file and if the metadata needs to be updated (from a sidecar)
			if ($this->resync_metadata === true) {
				$info = $this->getMetadata($file, $create->path, $create->kind, $create->extension);
				$attr = $existing->attributesToArray();
				foreach ($info as $key => $value) {
					if (array_key_exists($key, $attr)	// check if key exists, even if null
						&& (($existing->$key !== null && $value !== $existing->$key) || ($existing->$key === null && $value !== null && $value !== ''))
						&& $value != $existing->$key) {	// avoid false positives when comparing variables of different types (e.g string vs int)
						$metadataChanged = true;
						$existing->$key = $value;
					}
				}
			}

			if ($metadataChanged === true) {
				Logs::notice(__METHOD__, __LINE__, 'Updating metdata of existing photo.');
				$existing->save();

				$res = new PhotoResyncedException('This photo has been skipped because it\'s already in your library, but its metadata has been updated.');
			} else {
				Logs::notice(__METHOD__, __LINE__, 'Skipped upload of existing photo because skipDuplicates is activated');

				$res = new PhotoSkippedException('This photo has been skipped because it\'s already in your library.');
			}

			if ($this->delete_imported && !$create->is_uploaded) {
				@unlink($create->tmp_name);
			}

			throw $res;
		}
		//? else we do not skip duplicate and continue.
	}

	public function generate_thumbs(Create &$create, bool &$skip_db_entry_creation, bool &$no_error)
	{
		Logs::notice(__METHOD__, __LINE__, 'Nothing to generate, image is a duplicate');
	}
}
