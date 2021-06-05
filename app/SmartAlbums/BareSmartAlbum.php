<?php

namespace App\SmartAlbums;

use AccessControl;
use App\Models\Album;
use App\Models\Configs;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as BaseCollection;

class BareSmartAlbum extends Album
{
	/**
	 * @var Carbon
	 */
	public $created_at = null;

	/**
	 * @var Carbon
	 */
	public $updated_at = null;

	/**
	 * @var Collection[int]
	 */
	protected $albumIds = null;

	public function __construct()
	{
		parent::__construct();
		$this->albumIds = new BaseCollection();
		$this->created_at = new Carbon();
		$this->updated_at = new Carbon();
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
		if (AccessControl::is_admin()) {
			return $query;
		}

		if (AccessControl::is_logged_in()) {
			$query = $query->where('owner_id', '=', AccessControl::id())
				->orWhere(
					fn ($q) => $q->whereNotNull('album_id')
						->whereIn('album_id', $this->albumIds)
				);
		} else {
			$query = $query->whereIn('album_id', $this->albumIds);
		}

		if (Configs::get_value('public_photos_hidden', '1') === '0') {
			$query = $query->orWhere('public', '=', 1);
		}

		return $query;
	}

	/*------------------------- STRINGS --------------------------------- */
	public function str_parent_id()
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
