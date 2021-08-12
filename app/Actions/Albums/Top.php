<?php

namespace App\Actions\Albums;

use App\Actions\AlbumAuthorisationProvider;
use App\Facades\AccessControl;
use App\Models\Album;
use App\Models\Configs;
use Illuminate\Support\Collection as BaseCollection;
use Kalnoy\Nestedset\Collection as NsCollection;
use Kalnoy\Nestedset\QueryBuilder as NsQueryBuilder;

class Top
{
	private AlbumAuthorisationProvider $albumAuthorisationProvider;
	private string $sortingCol;
	private string $sortingOrder;

	public function __construct(AlbumAuthorisationProvider $albumAuthorisationProvider)
	{
		$this->albumAuthorisationProvider = $albumAuthorisationProvider;
		$this->sortingCol = Configs::get_value('sorting_Albums_col');
		$this->sortingOrder = Configs::get_value('sorting_Albums_order');
	}

	/**
	 * Returns an array of top-level albums (but not tag albums) visible
	 * to the current user.
	 *
	 * If the user is authenticated, then the result differentiates between
	 * albums which are owned by the user and "shared" albums which the
	 * user does not own, but is allowed to see.
	 * The term "shared album" might be a little bit misleading here.
	 * Albums which are owned by the user himself may also be shared (with
	 * other users.)
	 * Actually, in this context "shared albums" means "foreign albums".
	 *
	 * Note, the array may include password-protected albums that are not
	 * accessible (but are visible).
	 *
	 * @return array
	 */
	public function get(): array
	{
		$return = [
			'albums' => new BaseCollection(),
			'shared_albums' => new BaseCollection(),
		];

		/** @var NsQueryBuilder $query */
		$query = $this->albumAuthorisationProvider
			->applyVisibilityFilter(Album::query()->whereIsRoot());

		if (in_array($this->sortingCol, ['title', 'description'])) {
			/** @var NsCollection $albums */
			$albums = $query
				->orderBy('id', 'ASC')
				->get()
				->sortBy($this->sortingCol, SORT_NATURAL | SORT_FLAG_CASE, $this->sortingOrder === 'DESC');
		} else {
			/** @var NsCollection $albums */
			$albums = $query
				->orderBy($this->sortingCol, $this->sortingOrder)
				->orderBy('id', 'ASC')
				->get();
		}

		if (AccessControl::is_logged_in()) {
			$id = AccessControl::id();
			list($return['albums'], $return['shared_albums']) = $albums->partition(fn ($album) => $album->owner_id == $id);
		} else {
			$return['albums'] = $albums;
		}

		return $return;
	}
}
