<?php

namespace App\Actions\Albums;

use App\Actions\AlbumAuthorisationProvider;
use App\Contracts\InternalLycheeException;
use App\DTO\AlbumSortingCriterion;
use App\DTO\TopAlbums;
use App\Exceptions\Internal\InvalidOrderDirectionException;
use App\Facades\AccessControl;
use App\Factories\AlbumFactory;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use App\Models\TagAlbum;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Support\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Kalnoy\Nestedset\QueryBuilder as NsQueryBuilder;

class Top
{
	private AlbumAuthorisationProvider $albumAuthorisationProvider;
	private AlbumFactory $albumFactory;
	private AlbumSortingCriterion $sorting;

	/**
	 * @throws InvalidOrderDirectionException
	 */
	public function __construct(AlbumFactory $albumFactory, AlbumAuthorisationProvider $albumAuthorisationProvider)
	{
		$this->albumAuthorisationProvider = $albumAuthorisationProvider;
		$this->albumFactory = $albumFactory;
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
	 * @return TopAlbums
	 *
	 * @throws InternalLycheeException
	 */
	public function get(): TopAlbums
	{
		// Do not eagerly load the relation `photos` for each smart album.
		// On the albums overview, we only need a thumbnail for each album.
		/** @var Collection<BaseSmartAlbum> $smartAlbums */
		$smartAlbums = $this->albumFactory
			->getAllBuiltInSmartAlbums(false)
			->map(
				fn ($smartAlbum) => $this->albumAuthorisationProvider->isVisible($smartAlbum) ? $smartAlbum : null
			);

		$tagAlbumQuery = $this->albumAuthorisationProvider
			->applyVisibilityFilter(TagAlbum::query());
		/** @var Collection<TagAlbum> $tagAlbums */
		$tagAlbums = (new SortingDecorator($tagAlbumQuery))
			->orderBy($this->sorting->column, $this->sorting->order)
			->get();

		/** @var NsQueryBuilder $query */
		$query = $this->albumAuthorisationProvider
			->applyVisibilityFilter(Album::query()->whereIsRoot());

		if (AccessControl::is_logged_in()) {
			// For authenticated users we group albums by ownership.
			$albums = (new SortingDecorator($query))
				->orderBy('owner_id')
				->orderBy($this->sorting->column, $this->sorting->order)
				->get();

			$id = AccessControl::id();
			/**
			 * @var BaseCollection<Album> $a
			 * @var BaseCollection<Album> $b
			 */
			list($a, $b) = $albums->partition(fn ($album) => $album->owner_id === $id);

			return new TopAlbums($smartAlbums, $tagAlbums, $a->values(), $b->values());
		} else {
			// For anonymous users we don't want to implicitly expose
			// ownership via sorting.
			$albums = (new SortingDecorator($query))
				->orderBy($this->sorting->column, $this->sorting->order)
				->get();

			return new TopAlbums($smartAlbums, $tagAlbums, $albums);
		}
	}
}
