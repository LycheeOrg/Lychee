<?php

namespace App\SmartAlbums;

use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\SessionFunctions;
use App\Models\Album;
use App\Models\Configs;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as BaseCollection;

class SmartAlbum extends Album
{
	/**
	 * @var int
	 */
	public $id = '';

	/**
	 * @var string
	 */
	public $title = '';

	/**
	 * @var string
	 */
	public $description = '';

	/**
	 * @var bool
	 */
	public $public = '';

	/**
	 * @var bool
	 */
	public $viewable = false;

	/**
	 * @var Carbon
	 */
	public $created_at = null;

	/**
	 * fake password string.
	 *
	 * @var string
	 */
	public $password = '';

	/**
	 * @var AlbumFunctions
	 */
	protected $albumFunctions;

	/**
	 * @var Collection[int]
	 */
	protected $albumIds = null;

	/**
	 * @var SessionFunctions
	 */
	protected $sessionFunctions;

	/**
	 * Constructor use DDI.
	 *
	 * @param AlbumFunctions   $albumFunctions
	 * @param SessionFunctions $albumFunctions
	 */
	public function __construct(AlbumFunctions $albumFunctions, SessionFunctions $sessionFunctions)
	{
		parent::__construct();
		$this->albumFunctions = $albumFunctions;
		$this->sessionFunctions = $sessionFunctions;
		$this->albumIds = new BaseCollection();
		$this->created_at = new Carbon();
		$this->smart = true;
	}

	public function get_title()
	{
		return 'undefined';
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
		if (!$this->sessionFunctions->is_admin()) {
			$query = $query->whereIn('album_id', $this->albumIds);
		}

		if ($this->sessionFunctions->is_logged_in() && $this->sessionFunctions->id() > 0) {
			$query = $query->orWhere('owner_id', '=', $this->sessionFunctions->id());
		}

		return $query;
	}

	public function is_full_photo_visible()
	{
		return false;
	}

	public function str_parent_id()
	{
		return null;
	}

	public function is_downloadable()
	{
		return Configs::get_value('downloadable', '0') == '1';
	}

	public function is_share_button_visible()
	{
		return Configs::get_value('share_button_visible', '0');
	}

	// Parse date
	public function str_min_takestamp()
	{
		return '';
	}

	public function str_max_takestamp()
	{
		return '';
	}

	public function get_license()
	{
		return 'none';
	}

	public function is_public()
	{
		return false;
	}

	public function children()
	{
		return null;
	}
}
