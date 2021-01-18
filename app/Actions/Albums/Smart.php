<?php

namespace App\Actions\Albums;

use AccessControl;
use App\Actions\Albums\Extensions\PublicIds;
use App\Actions\Albums\Extensions\TopQuery;
use App\Factories\SmartFactory;
use App\ModelFunctions\SymLinkFunctions;

class Smart
{
	use TopQuery;

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

	public function __construct(SymLinkFunctions $symLinkFunctions, SmartFactory $smartFactory, Tag $tag)
	{
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
		$publicAlbums = resolve(PublicIds::class)->getPublicAlbumsId();
		$smartAlbums = $this->smartFactory->makeAll();

		foreach ($this->tag->get() as $tagAlbum) {
			$smartAlbums->push($tagAlbum);
		}

		/* @var SmartAlbum */
		foreach ($smartAlbums as $smartAlbum) {
			if (AccessControl::can_upload() || $smartAlbum->is_public()) {
				$smartAlbum->setAlbumIDs($publicAlbums);
				$return[$smartAlbum->title] = $smartAlbum->toReturnArray();
			}
		}

		if (empty($return)) {
			return null;
		}

		return $return;
	}
}
