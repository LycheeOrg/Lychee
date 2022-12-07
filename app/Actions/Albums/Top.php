<?php

namespace App\Actions\Albums;

use App\Contracts\InternalLycheeException;
use App\DTO\AlbumSortingCriterion;
use App\DTO\TopAlbums;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\InvalidOrderDirectionException;
use App\Factories\AlbumFactory;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Extensions\SortingDecorator;
use App\Models\TagAlbum;
use App\Policies\AlbumPolicy;
use App\Policies\AlbumQueryPolicy;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Kalnoy\Nestedset\QueryBuilder as NsQueryBuilder;

class Top
{
	private AlbumQueryPolicy $albumQueryPolicy;
	private AlbumFactory $albumFactory;
	private AlbumSortingCriterion $sorting;

	/**
	 * @throws InvalidOrderDirectionException
	 * @throws ConfigurationKeyMissingException
	 */
	public function __construct(AlbumFactory $albumFactory, AlbumQueryPolicy $albumQueryPolicy)
	{
		$this->albumQueryPolicy = $albumQueryPolicy;
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
		if (Configs::getValueAsBool('SA_enabled')) {
			// Do not eagerly load the relation `photos` for each smart album.
			// On the albums overview, we only need a thumbnail for each album.
			/** @var BaseCollection<BaseSmartAlbum> $smartAlbums */
			$smartAlbums = $this->albumFactory
				->getAllBuiltInSmartAlbums(false)
				->map(
					fn ($smartAlbum) => Gate::check(AlbumPolicy::IS_VISIBLE, $smartAlbum) ? $smartAlbum : null
				);
		} else {
			$smartAlbums = new BaseCollection();
		}

		$tagAlbumQuery = $this->albumQueryPolicy
			->applyVisibilityFilter(TagAlbum::query());
		/** @var BaseCollection<TagAlbum> $tagAlbums */
		$tagAlbums = (new SortingDecorator($tagAlbumQuery))
			->orderBy($this->sorting->column, $this->sorting->order)
			->get();

		/** @var NsQueryBuilder $query */
		$query = $this->albumQueryPolicy
			->applyVisibilityFilter(Album::query()->whereIsRoot());

		$userID = Auth::id();
		if ($userID !== null) {
			// For authenticated users we group albums by ownership.
			$albums = (new SortingDecorator($query))
				->orderBy(ColumnSortingType::OWNER_ID, OrderSortingType::ASC)
				->orderBy($this->sorting->column, $this->sorting->order)
				->get();

			/**
			 * @var BaseCollection<Album> $a
			 * @var BaseCollection<Album> $b
			 */
			list($a, $b) = $albums->partition(fn ($album) => $album->owner_id === $userID);

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
