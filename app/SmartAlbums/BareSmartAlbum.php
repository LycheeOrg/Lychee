<?php

namespace App\SmartAlbums;

use AccessControl;
use App\Models\Album;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as BaseCollection;

class BareSmartAlbum extends Album
{
	/**
	 * @var Carbon
	 */
	public $created_at = null;

	/**
	 * @var Collection[int]
	 */
	protected $albumIds = null;

	public function __construct()
	{
		parent::__construct();
		$this->albumIds = new BaseCollection();
		$this->created_at = new Carbon();
		$this->smart = true;
	}

	/**
	 * Set a restriction on the available albums.
	 *
	 * @param Collection[int] $albumIds
	 *
	 * @return void
	 */
	public function setAlbumIDs(BaseCollection $albumIds): void
	{
		$this->albumIds = $albumIds;
	}

	public function filter($query)
	{
		if (!AccessControl::is_admin()) {
			$query = $query->whereIn('album_id', $this->albumIds);
		}

		if (AccessControl::is_logged_in() && AccessControl::id() > 0) {
			$query = $query->orWhere('owner_id', '=', AccessControl::id());
		}

		return $query;
	}

	/*------------------------- STRINGS --------------------------------- */
	public function str_parent_id()
	{
		return '';
	}

	public function str_min_takestamp()
	{
		return '';
	}

	public function str_max_takestamp()
	{
		return '';
	}

	public function get_license(): string
	{
		return 'none';
	}

	/**
	 * We override this method so we can use it elsewhere. :).
	 */
	public function get_all_photos()
	{
		return $this->get_photos();
	}
}
