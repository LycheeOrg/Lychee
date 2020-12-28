<?php

namespace App\Actions\Albums;

use App\ModelFunctions\AlbumFunctions;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Extensions\CustomSort;
use Illuminate\Database\Eloquent\Collection;

class Tag
{
	use TopQuery;
	use CustomSort;

	/**
	 * @var AlbumFunctions
	 */
	public $albumFunctions;

	/**
	 * @var string
	 */
	private $sortingCol;

	/**
	 * @var string
	 */
	private $sortingOrder;

	public function __construct(AlbumFunctions $albumFunctions)
	{
		$this->albumFunctions = $albumFunctions;

		$this->sortingCol = Configs::get_value('sorting_Albums_col');
		$this->sortingOrder = Configs::get_value('sorting_Albums_order');
	}

	public function get(): Collection
	{
		$sql = $this->createTopleveAlbumsQuery()->where('smart', '=', true);

		return $this->customSort($sql, $this->sortingCol, $this->sortingOrder)
			->map(fn (Album $album) => $album->toTagAlbum());
	}
}
