<?php

namespace App\Actions\Photo\Strategies;

use App\Actions\Photo\Create;
use App\Actions\Photo\Extensions\Metadata;
use App\Exceptions\JsonWarning;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\Photo;
use Storage;

class StrategyDuplicate extends StrategyPhotoBase
{
	public $force_skip_duplicates;
	public $resync_metadata;
	public $delete_imported;

	public function __construct(
		bool $force_skip_duplicate,
		bool $resync_metadata,
		bool $delete_imported
	) {
		$this->force_skip_duplicate = $force_skip_duplicate;
		$this->resync_metadata = $resync_metadata;
		$this->delete_imported = $delete_imported;
	}

	public function storeFile(Create $create)
	{
		Logs::notice(__FILE__, __LINE__, 'Nothing to store, image is a duplicate');
	}

	public function hydrate(Create &$create, ?Photo &$existing = null, ?array $file = null)
	{
		$create->photo_Url = $existing->url;
		$create->path = Storage::path($create->path_prefix . $existing->url);
		$create->photo->thumbUrl = $existing->thumbUrl;
		$create->photo->thumb2x = $existing->thumb2x;
		$create->photo->medium = $existing->medium;
		$create->photo->medium2x = $existing->medium2x;
		$create->photo->small = $existing->small;
		$create->photo->small2x = $existing->small2x;
		$create->photo->livePhotoUrl = $existing->livePhotoUrl;
		$create->photo->livePhotoChecksum = $existing->livePhotoChecksum;
		$create->photo->checksum = $existing->checksum;
		$create->photo->type = $existing->type;
		$create->mimeType = $create->photo->type;

		// Photo already exists
		// Check if the user wants to skip duplicates
		if ($this->force_skip_duplicates || Configs::get_value('skip_duplicates', '0') === '1') {
			$metadataChanged = false;

			// Before we skip entirely, check if there is a sidecar file and if the metadata needs to be updated (from a sidecar)
			if ($this->resync_metadata === true) {
				$info = $this->getMetadata($file, $create->path, $create->kind, $create->extension);
				foreach ($info as $key => $value) {
					if ($existing->$key !== null && $value !== $existing->$key) {
						$metadataChanged = true;
						$existing->$key = $value;
					}
				}
			}

			if ($metadataChanged === true) {
				Logs::notice(__METHOD__, __LINE__, 'Updating metdata of existing photo.');
				$existing->save();

				$res = new JsonWarning('This photo has been skipped because it\'s already in your library, but its metadata has been updated.');
			} else {
				Logs::notice(__METHOD__, __LINE__, 'Skipped upload of existing photo because skipDuplicates is activated');

				$res = new JsonWarning('This photo has been skipped because it\'s already in your library.');
			}

			if ($this->delete_imported && !is_uploaded_file($create->tmp_name)) {
				@unlink($create->tmp_name);
			}

			throw $res;
		}
		//? else we do not skip duplicate and continue.
	}

	public function generate_thumbs(Create &$create, bool &$skip_db_entry_creation, bool &$no_error)
	{
		Logs::notice(__FILE__, __LINE__, 'Nothing to store, image is a duplicate');
	}
}
