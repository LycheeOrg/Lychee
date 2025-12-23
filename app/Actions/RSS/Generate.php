<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\RSS;

use App\Constants\PhotoAlbum as PA;
use App\Contracts\Exceptions\InternalLycheeException;
use App\Enum\SizeVariantType;
use App\Exceptions\Internal\FrameworkException;
use App\Models\Extensions\HasUrlGenerator;
use App\Models\Extensions\UTCBasedTimes;
use App\Models\Photo;
use App\Policies\PhotoQueryPolicy;
use App\Repositories\ConfigManager;
use Carbon\Exceptions\InvalidFormatException;
use Carbon\Exceptions\UnitException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Feed\FeedItem;

/**
 * @template T of object{id:string,title:string,description:?string,type:string,created_at:string,updated_at:string,album_id:string,album_title:string,short_path:string,filesize:int,storage_disk:string,size_variant_type:string,username:string}
 */
class Generate
{
	use HasUrlGenerator;
	use UTCBasedTimes;

	public function __construct(
		protected PhotoQueryPolicy $photo_query_policy,
		protected readonly ConfigManager $config_manager,
	) {
	}

	/**
	 * @param T $data
	 *
	 * @return FeedItem
	 *
	 * @throws BindingResolutionException
	 */
	private function toFeedItem(object $data): FeedItem
	{
		$page_link = route('gallery', ['albumId' => $data->album_id, 'photoId' => $data->id]);
		$feed_item = [
			'id' => $page_link,
			'title' => $data->title,
			'summary' => $data->description ?? '',
			'updated' => $this->asDateTime($data->updated_at),
			'link' => $page_link,
			'enclosure' => self::pathToUrl($data->short_path, $data->storage_disk, SizeVariantType::ORIGINAL),
			'enclosureType' => $data->type,
			'enclosureLength' => $data->filesize,
			'authorName' => $data->username,
			'category' => [$data->album_title],
		];

		return FeedItem::create($feed_item);
	}

	/**
	 * @return Collection<int,FeedItem>
	 *
	 * @throws InternalLycheeException
	 */
	public function do(): Collection
	{
		$rss_recent = $this->config_manager->getValueAsInt('rss_recent_days');
		$rss_max = $this->config_manager->getValueAsInt('rss_max_items');
		try {
			$now_minus = Carbon::now()->subDays($rss_recent)->toDateTimeString();
		} catch (UnitException|InvalidFormatException $e) {
			throw new FrameworkException('Date/Time component (Carbon)', $e);
		}

		/** @var Collection<int,T> $photos */
		$photos = $this->photo_query_policy
			->applySearchabilityFilter(
				query: Photo::query(),
				origin: null,
				include_nsfw: !$this->config_manager->getValueAsBool('hide_nsfw_in_rss')
			)
			->joinSub(DB::table(PA::PHOTO_ALBUM), 'outer_' . PA::PHOTO_ALBUM, 'photos.id', '=', 'outer_' . PA::PHOTO_ID)
			->join('base_albums', 'base_albums.id', '=', 'outer_' . PA::ALBUM_ID)
			->join('size_variants', 'size_variants.photo_id', '=', 'photos.id')
			->join('users', 'users.id', '=', 'photos.owner_id')
			->where('size_variants.type', '=', SizeVariantType::ORIGINAL->value)
			->select([
				'photos.id',
				'photos.title',
				'photos.description',
				'photos.type',
				'photos.updated_at',
				'outer_' . PA::ALBUM_ID,
				'base_albums.title as album_title',
				'size_variants.short_path',
				'size_variants.filesize',
				'size_variants.storage_disk',
				'users.username']
			)
			->where('photos.created_at', '>=', $now_minus)
			->limit($rss_max)
			->orderBy('photos.created_at', 'desc')
			->toBase() // We use toBase() to avoid the use of the Eloquent casts etc.
			->get();

		return $photos->map(fn (object $p) => $this->toFeedItem($p));
	}
}