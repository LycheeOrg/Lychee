<?php

namespace App\Actions\RSS;

use App\Models\Configs;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Spatie\Feed\FeedItem;

class Generate
{
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

		$photos = Photo::with('album', 'owner', 'size_variants_raw', 'size_variants_raw.sym_links')
			->where('created_at', '>=', $nowMinus)
			// we select photo which album IS PUBLICLY ACCESSIBLE
			// or PHOTO MARKED AS PUBLIC.
			// TODO: Replace this with a proper method of a class `PhotoAuthorizationProvider` in the same spirit like `AlbumAuthorizationProvider`
			->where(fn (Builder $q) => $q
				->where('public', '=', true)
				->orWhereHas('album', fn (Builder $q2) => $q2->where('public', '=', true))
			)
			->limit($rss_max)
			->get();

		return $photos->map(fn (Photo $p) => $this->toFeedItem($p));
	}
}
