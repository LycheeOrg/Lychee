<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Actions\Albums;

use App\Contracts\Exceptions\InternalLycheeException;
use App\DTO\AlbumSortingCriterion;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\InvalidOrderDirectionException;
use App\Legacy\V1\Resources\Collections\AlbumForestResource;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\Auth;
use Kalnoy\Nestedset\Collection as NsCollection;

final class Tree
{
	private AlbumQueryPolicy $albumQueryPolicy;
	private AlbumSortingCriterion $sorting;

	/**
	 * @throws InvalidOrderDirectionException
	 * @throws ConfigurationKeyMissingException
	 */
	public function __construct(AlbumQueryPolicy $albumQueryPolicy)
	{
		$this->albumQueryPolicy = $albumQueryPolicy;
		$this->sorting = AlbumSortingCriterion::createDefault();
	}

	/**
	 * @return AlbumForestResource
	 *
	 * @throws InternalLycheeException
	 */
	public function get(): AlbumForestResource
	{
		/*
		 * Note, strictly speaking
		 * {@link AlbumQueryPolicy::applyBrowsabilityFilter()}
		 * would be the correct function in order to scope the query below,
		 * because we only want albums which are browsable.
		 * But
		 * {@link AlbumQueryPolicy::applyBrowsabilityFilter()}
		 * is rather slow for large sets of albums (O(n²) runtime).
		 * Luckily,
		 * {@link AlbumQueryPolicy::applyReachabilityFilter()}
		 * is sufficient here, although it does only consider an album's
		 * reachability _locally_.
		 * We rely on `->toTree` below to remove orphaned sub-tress and hence
		 * only return a tree of browsable albums.
		 */
		$query = new SortingDecorator(
			$this->albumQueryPolicy->applyReachabilityFilter(Album::query())
		);
		if (Auth::check()) {
			// For authenticated users we group albums by ownership.
			$query->orderBy(ColumnSortingType::OWNER_ID, OrderSortingType::ASC);
		}
		$query->orderBy($this->sorting->column, $this->sorting->order);

		/** @var NsCollection<Album> $albums */
		$albums = $query->get();
		/** @var ?NsCollection<Album> $sharedAlbums */
		$sharedAlbums = null;
		$userID = Auth::id();
		if ($userID !== null) {
			// ATTENTION:
			// For this to work correctly, it is crucial that all child albums
			// below each top-level album have the same owner!
			// Otherwise, this partitioning tears apart albums of the same
			// (sub)-tree and then `toTree` will return garbage as it does
			// not find connected paths within `$albums` or `$sharedAlbums`,
			// resp.
			/** @var NsCollection<Album> $albums */
			/** @var ?NsCollection<Album> $sharedAlbums */
			list($albums, $sharedAlbums) = $albums->partition(fn (Album $album) => $album->owner_id === $userID);
		}

		// We must explicitly pass `null` as the ID of the root album
		// as there are several top-level albums below root.
		// Otherwise, `toTree` uses the ID of the album with the lowest
		// `_lft` value as the (wrong) root album.
		/** @var BaseCollection<int,\App\Contracts\Models\AbstractAlbum> $albumsTree */
		$albumsTree = $albums->toTree(null);
		/** @var BaseCollection<int,\App\Contracts\Models\AbstractAlbum> $sharedTree */
		$sharedTree = $sharedAlbums?->toTree(null);

		return new AlbumForestResource($albumsTree, $sharedTree);
	}
}
