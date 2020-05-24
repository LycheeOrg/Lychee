<?php

namespace App\SmartAlbums;

use App\Album;
use App\Configs;
use App\ModelFunctions\AlbumFunctions;
use Illuminate\Support\Carbon;

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

	public function __construct(AlbumFunctions $albumFunctions)
	{
		parent::__construct();
		$this->albumFunctions = $albumFunctions;
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
		return false;
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

	// 'thumbs' => [],
	// 'thumbs2x' => [],
	// 'types' => [],
}
