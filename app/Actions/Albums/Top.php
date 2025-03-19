<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Albums;

use App\Contracts\Exceptions\InternalLycheeException;
use App\DTO\AlbumSortingCriterion;
use App\DTO\TopAlbumDTO;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\InvalidOrderDirectionException;
use App\Factories\AlbumFactory;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use App\Models\TagAlbum;
use App\Policies\AlbumPolicy;
use App\Policies\AlbumQueryPolicy;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class Top
{
	private AlbumSortingCriterion $sorting;

	/**
	 * @throws InvalidOrderDirectionException
	 * @throws ConfigurationKeyMissingException
	 */
	public function __construct(
		private AlbumFactory $album_factory,
		private AlbumQueryPolicy $album_query_policy,
	)
	{
		$this->sorting = AlbumSortingCriterion::createDefault();
	}

	/**
	 * Returns the top-level albums (but not tag albums) visible
	 * to the current user.
	 *
	 * If the user is authenticated, then the result differentiates between
	 * albums which are owned by the user and "shared" albums which the
	 * user does not own, but is allowed to see.
	 * The term "shared album" might be a little misleading here.
	 * Albums which are owned by the user himself may also be shared (with
	 * other users.)
	 * Actually, in this context "shared albums" means "foreign albums".
	 *
	 * Note, the result may include password-protected albums that are not
	 * accessible (but are visible).
	 *
	 * @return TopAlbumDTO
	 *
	 * @throws InternalLycheeException
	 */
	public function get(): TopAlbumDTO
	{
		// Do not eagerly load the relation `photos` for each smart album.
		// On the albums overview, we only need a thumbnail for each album.
		/** @var BaseCollection<int,BaseSmartAlbum> $smartAlbums */
		$smart_albums = $this->album_factory
			->getAllBuiltInSmartAlbums(false)
			->filter(fn ($smart_album) => Gate::check(AlbumPolicy::CAN_SEE, $smart_album));

		$tag_album_query = $this->album_query_policy
			->applyVisibilityFilter(TagAlbum::query()->with(['access_permissions', 'owner']));

		/** @var BaseCollection<int,TagAlbum> $tagAlbums */
		$tag_albums = (new SortingDecorator($tag_album_query))
			->orderBy($this->sorting->column, $this->sorting->order)
			->get();

		/** @return AlbumBuilder $query */
		$query = $this->album_query_policy
			->applyVisibilityFilter(Album::query()->with(['access_permissions', 'owner'])->whereIsRoot());

		$user_i_d = Auth::id();
		if ($user_i_d !== null) {
			// For authenticated users we group albums by ownership.
			$albums = (new SortingDecorator($query))
				->orderBy(ColumnSortingType::OWNER_ID, OrderSortingType::ASC)
				->orderBy($this->sorting->column, $this->sorting->order)
				->get();

			/**
			 * @var BaseCollection<int,Album> $a
			 * @var BaseCollection<int,Album> $b
			 */
			list($a, $b) = $albums->partition(fn ($album) => $album->owner_id === $user_i_d);

			return new TopAlbumDTO($smart_albums, $tag_albums, $a->values(), $b->values());
		} else {
			// For anonymous users we don't want to implicitly expose
			// ownership via sorting.
			/** @var BaseCollection<int,Album> */
			$albums = (new SortingDecorator($query))
				->orderBy($this->sorting->column, $this->sorting->order)
				->get();

			return new TopAlbumDTO($smart_albums, $tag_albums, $albums);
		}
	}
}
