<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Configs;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\SymLinkFunctions;
use App\Photo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\Feed\FeedItem;

class RSSController extends Controller
{
	/**
	 * @var AlbumFunctions
	 */
	private $albumFunctions;

	/**
	 * @var SymLinkFunctions
	 */
	private $symLinkFunctions;

	/**
	 * @param AlbumFunctions   $albumFunctions
	 * @param SymLinkFunctions $symLinkFunctions
	 */
	public function __construct(AlbumFunctions $albumFunctions, SymLinkFunctions $symLinkFunctions)
	{
		$this->albumFunctions = $albumFunctions;
		$this->symLinkFunctions = $symLinkFunctions;
	}

	/**
	 * @return Collection
	 */
	public function getRSS()
	{
		$collection = collect([]);

		$photos = Photo::with('album', 'owner')
		->where('created_at', '>=', Carbon::now()->subDays(intval(Configs::get_value('recent_age', '1')))
		->toDateTimeString())
		->where(function ($q) {
			$q->whereIn('album_id',
				$this->albumFunctions->getPublicAlbums())
				->orWhere('public', '=', '1');
		})->get();

		$photos = $photos->map(function ($photo_model) {
			$photo = $photo_model->prepareData();
			$this->symLinkFunctions->getUrl($photo_model, $photo);
			$id = null;
			if ($photo_model->album_id != null) {
				$album = $photo_model->album;
				if (!$album->full_photo_visible()) {
					$photo_model->downgrade($photo);
				}
				$id = '#' . $photo_model->album_id . '/' . $photo_model->id;
			} else { // Unsorted
				if (Configs::get_value('full_photo', '1') != '1') {
					$photo_model->downgrade($photo);
				}
				$id = 'view?p=' . $photo_model->id;
			}

			return FeedItem::create([
				'id' => url('/' . $id),
				'title' => $photo_model->title,
				'summary' => $photo_model->description,
				'updated' => $photo_model->created_at,
				'link' => $photo['url'],
				'author' => $photo_model->owner->username, ]);
		});

		return $photos;
	}
}