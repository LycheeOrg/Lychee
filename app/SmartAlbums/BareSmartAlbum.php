<?php

namespace App\SmartAlbums;

use App\Facades\AccessControl;
use App\Models\Album;
use App\Models\Configs;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as BaseCollection;

/**
 * Class BareSmartAlbum.
 *
 * Extending this class from {@link \App\Models\Album} does not make much
 * sense.
 * For example, an album can (recursively) have sub-albums which is not
 * possible for smart albums.
 * Also, a smart album can neither be moved, copied nor deleted.
 * A smart album has no parent album neither.
 * In consequence, there are many properties and methods inherited from
 * {@link \App\Models\Album} which triggers errors and exceptions when
 * accidentally called for an object of this class.
 * It would be much cleaner, if {@link \App\Models\Album} and this class
 * implemented the same interface which defines those properties and
 * methods which both have in common.
 *
 * TODO: Refactor this.
 */
class BareSmartAlbum extends Album
{
	public Carbon $created_at;

	public Carbon $updated_at;

	/**
	 * @var BaseCollection[int]
	 */
	protected BaseCollection $albumIds;

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
	 * @param BaseCollection[int] $albumIds
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
}
