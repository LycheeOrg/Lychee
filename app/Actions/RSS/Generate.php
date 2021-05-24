<?php

namespace App\Actions\RSS;

use App\Actions\Albums\Extensions\PublicIds;
use App\ModelFunctions\SymLinkFunctions;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Spatie\Feed\FeedItem;

class Generate
{
	/**
	 * @var SymLinkFunctions
	 */
	private $symLinkFunctions;

	/**
	 * @param SymLinkFunctions $symLinkFunctions
	 */
	public function __construct(
		SymLinkFunctions $symLinkFunctions
	) {
		$this->symLinkFunctions = $symLinkFunctions;
	}

	private function make_enclosure(array $photo_array)
	{
		$enclosure = new \stdClass();

		$path = public_path($photo_array['url']);
		$enclosure->length = File::size($path);
		$enclosure->mime_type = File::mimeType($path);
		$enclosure->url = url('/' . $photo_array['url']);

		return $enclosure;
	}

	private function create_link(Photo $photo_model, array &$photo_array)
	{
		if ($photo_model->album_id != null) {
			if (!$photo_model->album->is_full_photo_visible()) {
				$photo_model->downgrade($photo_array);
			}

			return '#' . $photo_model->album_id . '/' . $photo_model->id;
		}

		if (Configs::get_value('full_photo', '1') != '1') {
			$photo_model->downgrade($photo_array);
		}

		return 'view?p=' . $photo_model->id;
	}

	private function toFeedItem(Photo $photo_model)
	{
		$photo_array = $photo_model->toReturnArray();

		$this->symLinkFunctions->getUrl($photo_model, $photo_array);

		$photo_array['url'] = $photo_array['url'] ?: ($photo_array['medium2x'] ?: $photo_array['medium']);
		// TODO: this will need to be fixed for s3 and when the upload folder is NOT the Lychee folder.
		$enclosure = $this->make_enclosure($photo_array);

		$id = $this->create_link($photo_model, $photo_array);

		return FeedItem::create([
			'id' => url('/' . $id),
			'title' => $photo_model->title,
			'summary' => $photo_model->description,
			'updated' => $photo_model->updated_at,
			'link' => $photo_array['url'],
			'enclosure' => $enclosure->url,
			'enclosureLength' => $enclosure->length,
			'enclosureType' => $enclosure->mime_type,
			'author' => $photo_model->owner->username,
		]);
	}

	public function do()
	{
		$publicIds = resolve(PublicIds::class)->getNotAccessible();
		$rss_recent = intval(Configs::get_value('rss_recent_days', '7'));
		$rss_max = Configs::get_Value('rss_max_items', '100');
		$nowMinus = Carbon::now()->subDays($rss_recent)->toDateTimeString();

		$photos = Photo::with('album', 'owner')
			->where('created_at', '>=', $nowMinus)
			// we select photo which album IS PUBLICALLY ACCESSIBLE
			// or PHOTO MARKED AS PUBLIC.
			->where(fn ($q) => $q->whereIn('album_id', $publicIds)->orWhere('public', '=', '1'))
			->limit($rss_max)
			->get();

		return $photos->map(fn (Photo $p) => $this->toFeedItem($p));
	}
}
