<?php

namespace App\SmartAlbums;

use App\Album;
use App\Configs;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\SessionFunctions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

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
	public $visible_hidden = false;

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
		$this->albumIds = new Collection();
		$this->created_at = new Carbon();
	}

	public function get_title()
	{
		return 'undefined';
	}

	/**
	 * Set a restriction on the available albums.
	 *
	 * @return void
	 */
	public function setAlbumIDs(Collection $albumIds): void
	{
		$this->albumIds = $albumIds;
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

	// 'thumbs' => [],
	// 'thumbs2x' => [],
	// 'types' => [],
}
