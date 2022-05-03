<?php

namespace App\Actions\Albums;

use App\Actions\AlbumAuthorisationProvider;
use App\Contracts\InternalLycheeException;
use App\DTO\AlbumSortingCriterion;
use App\DTO\AlbumTree;
use App\Exceptions\Internal\InvalidOrderDirectionException;
use App\Facades\AccessControl;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use Kalnoy\Nestedset\Collection as NsCollection;

class Tree
{
	private AlbumAuthorisationProvider $albumAuthorisationProvider;
	private AlbumSortingCriterion $sorting;

	/**
	 * @throws InvalidOrderDirectionException
	 */
	public function __construct(AlbumAuthorisationProvider $albumAuthorisationProvider)
	{
		$this->albumAuthorisationProvider = $albumAuthorisationProvider;
		$this->sorting = AlbumSortingCriterion::createDefault();
	}

	/**
	 * @return AlbumTree
	 *
	 * @throws InternalLycheeException
	 */
	public function get(): AlbumTree
	{
		/*
		 * Note, strictly speaking
		 * {@link AlbumAuthorisationProvider::applyBrowsabilityFilter()}
		 * would be the correct function in order to scope the query below,
		 * because we only want albums which are browsable.
		 * But
		 * {@link AlbumAuthorisationProvider::applyBrowsabilityFilter()}
		 * is rather slow for large sets of albums (O(n²) runtime).
		 * Luckily,
		 * {@link AlbumAuthorisationProvider::applyReachabilityFilter()}
		 * is sufficient here, although it does only consider an album's
		 * reachability _locally_.
		 * We rely on `->toTree` below to remove orphaned sub-tress and hence
		 * only return a tree of browsable albums.
		 */
		$query = new SortingDecorator(
			$this->albumAuthorisationProvider->applyReachabilityFilter(Album::query())
		);
		if (AccessControl::is_logged_in()) {
			// For authenticated users we group albums by ownership.
			$query->orderBy('owner_id');
		}
		$query->orderBy($this->sorting->column, $this->sorting->order);

		/** @var NsCollection $albums */
		$albums = $query->get();
		/** @var ?NsCollection $sharedAlbums */
		$sharedAlbums = null;
		if (AccessControl::is_logged_in()) {
			$id = AccessControl::id();
			// ATTENTION:
			// For this to work correctly, it is crucial that all child albums
			// below each top-level album have the same owner!
			// Otherwise, this partitioning tears apart albums of the same
			// (sub)-tree and then `toTree` will return garbage as it does
			// not find connected paths within `$albums` or `$sharedAlbums`,
			// resp.
			list($albums, $sharedAlbums) = $albums->partition(fn ($album) => $album->owner_id == $id);
		}

		// We must explicitly pass `null` as the ID of the root album
		// as there are several top-level albums below root.
		// Otherwise, `toTree` uses the ID of the album with the lowest
		// `_lft` value as the (wrong) root album.
		return new AlbumTree($albums->toTree(null), $sharedAlbums?->toTree(null));
	}
}
