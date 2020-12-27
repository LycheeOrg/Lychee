<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\ModelFunctions;

use AccessControl;
use App\Actions\Album\Cast as AlbumCast;
use App\Actions\Albums\PublicIds;
use App\Actions\Albums\Tag;
use App\Actions\Albums\Top;
use App\Actions\ReadAccessFunctions;
use App\Models\Album;
use App\SmartAlbums\SmartFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

class AlbumsFunctions
{
	use PublicIds;

	/**
	 * @var readAccessFunctions
	 */
	private $readAccessFunctions;

	/**
	 * @var AlbumFunctions
	 */
	private $albumFunctions;

	/**
	 * @var SymLinkFunctions
	 */
	private $symLinkFunctions;

	/**
	 * @var SmartFactory
	 */
	private $smartFactory;

	/**
	 * @var Top
	 */
	private $top;

	/**
	 * @var Tag
	 */
	private $tag;

	/**
	 * AlbumFunctions constructor.
	 *
	 * @param SessionFunctions    $sessionFunctions
	 * @param ReadAccessFunctions $readAccessFunctions
	 * @param SymLinkFunctions    $symLinkFunctions
	 */
	public function __construct(ReadAccessFunctions $readAccessFunctions, AlbumFunctions $albumFunctions, SymLinkFunctions $symLinkFunctions, SmartFactory $smartFactory, Top $top, Tag $tag)
	{
		$this->readAccessFunctions = $readAccessFunctions;
		$this->albumFunctions = $albumFunctions;
		$this->symLinkFunctions = $symLinkFunctions;
		$this->smartFactory = $smartFactory;
		$this->top = $top;
		$this->tag = $tag;
	}

	/**
	 * ? Only used in AlbumsController
	 * Given a list of albums, generate an array to be returned.
	 *
	 * @param Collection[Album]        $albums
	 * @param Collection[Collection[]] $children
	 *
	 * @return array
	 */
	public function prepare_albums(BaseCollection $albums, BaseCollection $children)
	{
		$return = [];
		foreach ($albums->keys() as $key) {
			$album_array = AlbumCast::toArrayWith($albums[$key], $children[$key]);

			if (AccessControl::is_logged_in()) {
				$album_array['owner'] = $albums[$key]->owner->name();
			}

			if ($this->readAccessFunctions->album($albums[$key]) === 1) {
				$thumbs = $this->albumFunctions->get_thumbs($albums[$key], $children[$key]);
				$this->albumFunctions->set_thumbs($album_array, $thumbs);
				$this->albumFunctions->set_thumbs_children($album_array['albums'], $thumbs[1]);
			}

			// Add to return
			$return[] = $album_array;
		}

		return $return;
	}

	/**
	 * @param array[Collection[Album]] $albums_list
	 *
	 * @return array
	 */
	public function get_children(array $albums_list, $includePassProtected = false)
	{
		$return = [];
		foreach ($albums_list as $kind => $albums) {
			$return[$kind] = new BaseCollection();

			$albums->each(function ($album, $key) use ($return, $kind, $includePassProtected) {
				$children = new Collection();

				if ($this->readAccessFunctions->album($album) === 1) {
					$children = $this->albumFunctions->get_children($album, 0, $includePassProtected);
				}

				$return[$kind]->put($key, $children);
			});
		}

		return $return;
	}
}
