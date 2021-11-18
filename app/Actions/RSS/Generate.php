<?php

namespace App\Actions\RSS;

use App\Actions\PhotoAuthorisationProvider;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Spatie\Feed\FeedItem;

class Generate
{
	protected PhotoAuthorisationProvider $photoAuthorisationProvider;

	public function __construct(PhotoAuthorisationProvider $photoAuthorisationProvider)
	{
		$this->photoAuthorisationProvider = $photoAuthorisationProvider;
	}

	private function create_link_to_page(Photo $photo_model): string
	{
		if ($photo_model->album_id != null) {
			return url('/#' . $photo_model->album_id . '/' . $photo_model->id);
		}

		return url('/view?p=' . $photo_model->id);
	}

	private function toFeedItem(Photo $photo_model): FeedItem
	{
		$page_link = $this->create_link_to_page($photo_model);
		$sizeVariant = $photo_model->size_variants->getSizeVariant(SizeVariant::ORIGINAL);

		return FeedItem::create([
			'id' => $page_link,
			'title' => $photo_model->title,
			'summary' => $photo_model->description ?? '',
			'updated' => $photo_model->updated_at,
			'link' => $page_link,
			'enclosure' => $sizeVariant->url,
			'enclosureLength' => Storage::size($sizeVariant->short_path),
			'enclosureType' => $photo_model->type,
			'author' => $photo_model->owner->username,
		]);
	}

	public function do()
	{
		$rss_recent = intval(Configs::get_value('rss_recent_days', '7'));
		$rss_max = Configs::get_Value('rss_max_items', '100');
		$nowMinus = Carbon::now()->subDays($rss_recent)->toDateTimeString();

		$photos = $this->photoAuthorisationProvider
			->applySearchabilityFilter(
				Photo::with('album', 'owner', 'size_variants_raw', 'size_variants_raw.sym_links')
			)
			->where('photos.created_at', '>=', $nowMinus)
			->limit($rss_max)
			->get();

		return $photos->map(fn (Photo $p) => $this->toFeedItem($p));
	}
}
