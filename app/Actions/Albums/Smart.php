<?php

namespace App\Actions\Albums;

use App\Actions\AlbumAuthorisationProvider;
use App\Facades\AccessControl;
use App\Factories\AlbumFactory;
use App\Models\Configs;
use App\Models\TagAlbum;
use App\SmartAlbums\BaseSmartAlbum;

class Smart
{
	private AlbumAuthorisationProvider $albumAuthorisationProvider;
	private AlbumFactory $albumFactory;
	private string $sortingCol;
	private string $sortingOrder;

	public function __construct(AlbumFactory $albumFactory, AlbumAuthorisationProvider $albumAuthorisationProvider)
	{
		$this->albumAuthorisationProvider = $albumAuthorisationProvider;
		$this->albumFactory = $albumFactory;
		$this->sortingCol = Configs::get_value('sorting_Albums_col');
		$this->sortingOrder = Configs::get_value('sorting_Albums_order');
	}

	/**
	 * Returns the array of smart albums visible to the current user.
	 *
	 * The array includes the built-in smart albums and the user-defined
	 * smart albums (i.e. tag albums).
	 * Note, the array may include password-protected albums that are visible
	 * but not accessible.
	 *
	 * @return array[BaseAlbum] the array of smart albums
	 */
	public function get(): array
	{
		$return = [];
		$smartAlbums = $this->albumFactory->getAllBuiltInSmartAlbums();
		/** @var BaseSmartAlbum $smartAlbum */
		foreach ($smartAlbums as $smartAlbum) {
			if (AccessControl::can_upload() || $smartAlbum->public) {
				$return[$smartAlbum->id] = $smartAlbum;
			}
		}

		$tagAlbumQuery = $this->albumAuthorisationProvider
			->applyVisibilityFilter(TagAlbum::query());
		if (in_array($this->sortingCol, ['title', 'description'])) {
			$tagAlbums = $tagAlbumQuery
				->orderBy('id', 'ASC')
				->get()
				->sortBy($this->sortingCol, SORT_NATURAL | SORT_FLAG_CASE, $this->sortingOrder === 'DESC');
		} else {
			$tagAlbums = $tagAlbumQuery
				->orderBy($this->sortingCol, $this->sortingOrder)
				->orderBy('id', 'ASC')
				->get();
		}

		/** @var TagAlbum $tagAlbum */
		foreach ($tagAlbums as $tagAlbum) {
			$return[$tagAlbum->id] = $tagAlbum;
		}

		return $return;
	}
}
