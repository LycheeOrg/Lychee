<?php

namespace App\Actions\Photo;

use App\Actions\Photo\Extensions\Checks;
use App\Actions\Photo\Extensions\Checksum;
use App\Actions\Photo\Extensions\Constants;
use App\Actions\Photo\Extensions\ImageEditing;
use App\Actions\Photo\Extensions\ParentAlbum;
use App\Actions\Photo\Extensions\Save;
use App\Actions\Photo\Extensions\VideoEditing;
use App\Actions\Photo\Strategies\StrategyDuplicate;
use App\Actions\Photo\Strategies\StrategyPhoto;
use App\Facades\Helpers;
use App\Models\Album;
use App\Models\Color;
use App\Models\Logs;
use App\Models\Photo;
use ColorThief\ColorThief;
use Illuminate\Support\Facades\Storage;

class Create
{
	use Checks;
	use Checksum;
	use Constants;
	use ImageEditing;
	use ParentAlbum;
	use Save;
	use VideoEditing;

	public bool $public;
	public bool $star;
	public ?int $albumID;
	public ?Album $parentAlbum = null;
	public ?Photo $photo = null;
	public string $photo_filename;
	public string $kind;
	public string $extension;
	public string $path_prefix;
	public string $path;
	public string $tmp_name;
	public string $mimeType;
	public array $info = [];
	public ?Photo $livePhotoPartner = null;
	public bool $is_uploaded;

	public function add(
		array $file,
		$albumID_in = 0,
		bool $delete_imported = false,
		bool $skip_duplicates = false,
		bool $import_via_symlink = false,
		bool $resync_metadata = false
	) {
		// Check permissions
		$this->checkPermissions();

		$this->public = 0;
		$this->star = 0;
		$this->albumID = null;

		$this->initParentId($albumID_in);

		// Verify extension
		$this->extension = Helpers::getExtension($file['name'], false);
		$this->mimeType = $file['type'];
		$this->kind = $this->file_type($file, $this->extension);

		// Generate id
		$this->photo = new Photo();
		$this->photo->id = Helpers::generateID();

		// Set paths
		$this->tmp_name = $file['tmp_name'];
		$this->is_uploaded = is_uploaded_file($file['tmp_name']);
		$this->path_prefix = ($this->kind != 'raw') ? 'big/' : 'raw/';

		// Calculate checksum
		$this->photo->checksum = $this->checksum($this->tmp_name);
		$duplicate = $this->get_duplicate($this->photo->checksum);
		$exists = ($duplicate !== null);

		$this->photo_Url = substr($this->photo->checksum, 0, 32) . $this->extension;
		$this->path = Storage::path($this->path_prefix . $this->photo_Url);

		/*
		 * ! From here we need to use a Strategy depending if we have
		 * ! a duplicate
		 * ! a "normal" picture
		 * ! a live picture
		 * ! a video
		 */

		if (!$duplicate) {
			$strategy = new StrategyPhoto($import_via_symlink);
		} else {
			$strategy = new StrategyDuplicate($skip_duplicates, $resync_metadata, $delete_imported);
		}

		$strategy->storeFile($this);
		$strategy->hydrate($this, $duplicate, $file);

		// set $this->info
		$strategy->loadMetadata($this, $file);

		$strategy->setParentAndOwnership($this);

		// set $this->livePhotoPartner
		$strategy->findLivePartner($this);

		$no_error = true;
		$skip_db_entry_creation = false;

		$strategy->generate_thumbs($this, $skip_db_entry_creation, $no_error);

		// In case it's a live photo and we've uploaded the video
		if ($skip_db_entry_creation === true) {
			$res = $this->livePhotoPartner->id;
		} else {
			$res = $this->save($this->photo);
		}

		//extract the main color and palette
		$palette = ColorThief::getPalette($this->path, 5);

		if (!empty($palette)) {
			foreach ($palette as $index => $value) {
				$color = new Color([
					'r' => $value[0],
					'g' => $value[1],
					'b' => $value[2],
					'is_main' => ($index === 0 ? 1 : 0),
					'photo_id' => $this->photo->id,
				]);
				$this->photo->colors()->save($color);
			}
		}

		if ($delete_imported && !$this->is_uploaded && ($exists || !$import_via_symlink) && !@unlink($this->tmp_name)) {
			Logs::warning(__METHOD__, __LINE__, 'Failed to delete file (' . $this->tmp_name . ')');
		}

		return $res;
	}
}
