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
use App\Assets\Helpers;
use App\Http\Livewire\Album;
use App\Models\Logs;
use App\Models\Photo;
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

	/** @var int */
	public $public;
	/** @var int */
	public $star;
	/** @var int|null */
	public $albumID;
	/** @var Album|null */
	public $parentAlbum;
	/** @var Photo */
	public $photo;
	/** @var string */
	public $photo_Url;
	/** @var string */
	public $kind;
	/** @var string */
	public $extension;
	/** @var string */
	public $path_prefix;
	/** @var string */
	public $tmp_name;
	/** @var string */
	public $mimeType;
	/** @var array */
	public $info;
	public $livePhotoPartner;
	/** @var bool */
	public $is_uploaded;

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
		$this->photo_Url = md5(microtime()) . $this->extension;
		$this->path_prefix = ($this->kind != 'raw') ? 'big/' : 'raw/';
		$this->path = Storage::path($this->path_prefix . $this->photo_Url);

		// Calculate checksum
		$this->photo->checksum = $this->checksum($this->tmp_name);
		$duplicate = $this->get_duplicate($this->photo->checksum);
		$exists = ($duplicate !== null);

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

		if ($delete_imported && !$this->is_uploaded && ($exists || !$import_via_symlink) && !@unlink($this->tmp_name)) {
			Logs::warning(__METHOD__, __LINE__, 'Failed to delete file (' . $this->tmp_name . ')');
		}

		return $res;
	}
}
