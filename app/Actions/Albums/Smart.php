<?php

namespace App\Actions\Albums;

use AccessControl;
use App\Actions\Album\Cast as AlbumCast;
use App\Factories\SmartFactory;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\AlbumsFunctions;
use App\ModelFunctions\SymLinkFunctions;

class Smart
{
	use TopQuery;
	use PublicIds;

	/**
	 * @var AlbumFunctions
	 */
	public $albumFunctions;

	/**
	 * @var AlbumsFunctions
	 */
	public $albumsFunctions;

	/**
	 * @var SymLinkFunctions
	 */
	public $symLinkFunctions;

	/**
	 * @var Tag
	 */
	public $tag;

	/**
	 * @var SmartFactory
	 */
	public $smartFactory;

	public function __construct(AlbumsFunctions $albumsFunctions, SymLinkFunctions $symLinkFunctions, SmartFactory $smartFactory, Tag $tag)
	{
		$this->albumsFunctions = $albumsFunctions;
		$this->symLinkFunctions = $symLinkFunctions;
		$this->smartFactory = $smartFactory;
		$this->tag = $tag;
	}

	/**
	 * Returns an array of top-level albums and shared albums visible to
	 * the current user.
	 * Note: the array may include password-protected albums that are not
	 * accessible (but are visible).
	 *
	 * @return array[Collection[Album]]|null
	 */
	public function get(): ?array
	{
		/**
		 * Initialize return var.
		 */
		$return = [];

		/**
		 * @var Collection[SmartAlbum]
		 */
		$publicAlbums = $this->getPublicAlbumsId();
		$smartAlbums = $this->smartFactory->makeAll();

		foreach ($this->tag->get() as $tagAlbum) {
			$smartAlbums->push($tagAlbum);
		}

		foreach ($smartAlbums as $smartAlbum) {
			if (AccessControl::can_upload() || $smartAlbum->is_public()) {
				$smartAlbum->setAlbumIDs($publicAlbums);
				$return[$smartAlbum->get_title()] = $smartAlbum->toReturnArray();
				AlbumCast::getThumbs($return[$smartAlbum->get_title()], $smartAlbum, $this->symLinkFunctions);
			}
		}

		if (empty($return)) {
			return null;
		}

		return $return;
	}
}
