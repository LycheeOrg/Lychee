<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\RSS;

use App\Contracts\Exceptions\InternalLycheeException;
use App\Exceptions\Internal\FrameworkException;
use App\Models\Configs;
use App\Models\Photo;
use App\Policies\PhotoQueryPolicy;
use Carbon\Exceptions\InvalidFormatException;
use Carbon\Exceptions\UnitException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\Feed\FeedItem;

class Generate
{
	protected PhotoQueryPolicy $photoQueryPolicy;

	public function __construct(PhotoQueryPolicy $photoQueryPolicy)
	{
		$this->photoQueryPolicy = $photoQueryPolicy;
	}

	private function create_link_to_page(Photo $photo_model): string
	{
		if ($photo_model->album_id !== null) {
			return url('/gallery/' . $photo_model->album_id . '/' . $photo_model->id);
		}

		return url('/view?p=' . $photo_model->id);
	}

	private function toFeedItem(Photo $photo_model): FeedItem
	{
		$page_link = $this->create_link_to_page($photo_model);
		$sizeVariant = $photo_model->size_variants->getOriginal();
		$feedItem = [
			'id' => $page_link,
			'title' => $photo_model->title,
			'summary' => $photo_model->description ?? '',
			'updated' => $photo_model->updated_at,
			'link' => $page_link,
			'enclosure' => $sizeVariant->url,
			'enclosureType' => $photo_model->type,
			'enclosureLength' => $sizeVariant->filesize,
			'authorName' => $photo_model->owner->username,
		];

		return FeedItem::create($feedItem);
	}

	/**
	 * @return Collection<int,FeedItem>
	 *
	 * @throws InternalLycheeException
	 */
	public function do(): Collection
	{
		$rss_recent = Configs::getValueAsInt('rss_recent_days');
		$rss_max = Configs::getValueAsInt('rss_max_items');
		try {
			$nowMinus = Carbon::now()->subDays($rss_recent)->toDateTimeString();
		} catch (UnitException|InvalidFormatException $e) {
			throw new FrameworkException('Date/Time component (Carbon)', $e);
		}

		/** @var Collection<int,Photo> $photos */
		$photos = $this->photoQueryPolicy
			->applySearchabilityFilter(
				query: Photo::query()->with(['album', 'owner', 'size_variants', 'size_variants.sym_links']),
				origin: null,
				include_nsfw: !Configs::getValueAsBool('hide_nsfw_in_rss')
			)
			->where('photos.created_at', '>=', $nowMinus)
			->limit($rss_max)
			->get();

		return $photos->map(fn (Photo $p) => $this->toFeedItem($p));
	}
}
