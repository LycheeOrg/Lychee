<?php

namespace App\Actions\Albums;

use App\Actions\AlbumAuthorisationProvider;
use App\Contracts\InternalLycheeException;
use App\Facades\AccessControl;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Extensions\SortingDecorator;
use Illuminate\Support\Collection as BaseCollection;
use Kalnoy\Nestedset\QueryBuilder as NsQueryBuilder;

class Top
{
	private AlbumAuthorisationProvider $albumAuthorisationProvider;
	private string $sortingCol;
	private string $sortingOrder;

	public function __construct(AlbumAuthorisationProvider $albumAuthorisationProvider)
	{
		$this->albumAuthorisationProvider = $albumAuthorisationProvider;
		$this->sortingCol = Configs::get_value('sorting_Albums_col', 'created_at');
		$this->sortingOrder = Configs::get_value('sorting_Albums_order', 'ASC');
	}

	/**
	 * Returns an array of top-level albums (but not tag albums) visible
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
	 * Note, the array may include password-protected albums that are not
	 * accessible (but are visible).
	 *
	 * @return array{albums: BaseCollection, shared_albums: BaseCollection}
	 *
	 * @throws InternalLycheeException
	 */
	public function get(): array
	{
		/** @var NsQueryBuilder $query */
		$query = $this->albumAuthorisationProvider
			->applyVisibilityFilter(Album::query()->whereIsRoot());

		if (AccessControl::is_logged_in()) {
			// For authenticated users we group albums by ownership.
			$albums = (new SortingDecorator($query))
			->orderBy('owner_id')
			->orderBy($this->sortingCol, $this->sortingOrder)
			->get();

			$id = AccessControl::id();
			list($a, $b) = $albums->partition(fn ($album) => $album->owner_id == $id);
			$return = [
				'albums' => $a->values(),
				'shared_albums' => $b->values(),
			];
		} else {
			// For anonymous users we don't want to implicitly expose
			// ownership via sorting.
			$albums = (new SortingDecorator($query))
				->orderBy($this->sortingCol, $this->sortingOrder)
				->get();

			$return = [
				'albums' => $albums,
				'shared_albums' => new BaseCollection(),
			];
		}

		return $return;
	}
}
