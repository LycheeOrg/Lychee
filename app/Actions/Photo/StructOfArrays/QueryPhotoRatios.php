<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\StructOfArrays;

use App\Assets\DbBool;
use App\Contracts\Models\AbstractAlbum;
use App\DTO\PhotoSortingCriterion;
use App\Eloquent\FixedQueryBuilder;
use App\Enum\OrderSortingType;
use App\Enum\PhotoThumbInfoType;
use App\Enum\SizeVariantType;
use App\Enum\VisibilityType;
use App\Http\Resources\V3\PhotoRatioResource;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use App\Models\Photo;
use App\Models\User;
use App\Repositories\ConfigManager;
use App\Services\Image\FileExtensionService;
use App\Services\PhotoBucketComputer;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;

/**
 * Query logic for `GET /api/v3/Albums/{album_id}/Photos`.
 *
 * `toBase()`-only, one flat query — no Eloquent hydration, no
 * `PhotoResource`/relation eager-loading. The three `size_variants`
 * `LEFT JOIN`s remain exactly 3 fixed joins regardless of album photo count.
 */
class QueryPhotoRatios
{
	use ResolvesPhotoSource;

	public function __construct(
		private readonly ConfigManager $config_manager,
		private readonly FileExtensionService $file_extension_service,
		private readonly PhotoBucketComputer $bucket_computer,
	) {
	}

	public function do(AbstractAlbum $album, ?User $user): PhotoRatioResource
	{
		$sorting = $this->resolveEffectiveSorting($album);
		$is_regular_album = $album instanceof Album;

		$rating_enabled = $this->config_manager->getValueAsBool('rating_enabled');
		// PhotoPolicy::canReadRatings() ignores its own $photo argument
		// entirely (mirrors PhotoResource's own double-check exactly) - safe
		// to evaluate once per request instead of once per (Eloquent) photo.
		$can_read_ratings = $rating_enabled && ($user !== null || $this->config_manager->getValueAsBool('rating_public'));

		$overlay = $this->config_manager->getValueAsEnum('display_thumb_photo_overlay', VisibilityType::class) ?? VisibilityType::NEVER;
		$thumb_info_mode = $this->config_manager->getValueAsEnum('photo_thumb_info', PhotoThumbInfoType::class) ?? PhotoThumbInfoType::TITLE;
		$thumb_infos_on = $overlay !== VisibilityType::NEVER && $thumb_info_mode === PhotoThumbInfoType::DESCRIPTION;
		$tags_on = $overlay !== VisibilityType::NEVER && $thumb_info_mode === PhotoThumbInfoType::TITLE && $this->config_manager->getValueAsBool('photo_thumb_tags_enabled');
		$blank_titles = $this->config_manager->getValueAsBool('file_name_hidden') && $user === null;

		$query = $this->resolvePhotoQuery($album, $user);
		$this->joinRatioSizeVariants($query);

		$select = [
			'photos.id',
			'photos.title',
			'photos.title_base',
			'photos.type',
			'photos.owner_id',
			'photos.is_highlighted',
			'photos.is_validated',
			'photos.taken_at',
			'photos.created_at',
			'photos.taken_at_orig_tz',
			'photos.live_photo_short_path',
			// Always selected, independent of $can_read_ratings: a
			// non-`Album` source needs the raw value for live bucket
			// computation (RATING_AVG may be the effective sort column) even
			// when the viewer isn't permitted to see ratings - the output
			// array below still only ever gets populated when
			// $can_read_ratings is true, so nothing extra reaches the
			// response.
			'photos.rating_avg',
		];
		if ($is_regular_album) {
			$select[] = 'photo_album.bucket_id';
		}
		$selects_raw = ['COALESCE(sv_original.ratio, sv_medium.ratio, sv_small.ratio, 1) as ratio'];

		if ($can_read_ratings && $user !== null) {
			$query->leftJoin('photo_ratings as pr', function (JoinClause $join) use ($user): void {
				$join->on('pr.photo_id', '=', 'photos.id')->where('pr.user_id', '=', $user->id);
			});
			$select[] = 'pr.rating as rating_user';
		}

		if ($thumb_infos_on) {
			$select[] = 'photos.description';
		}

		if ($tags_on) {
			$this->joinTagAggregation($query);
			$select[] = 'tag_agg.tag_names';
		}

		if ($is_regular_album) {
			$direction = $sorting->order === OrderSortingType::DESC ? 'desc' : 'asc';
			// Order by bucket_id first (mirrors QueryPhotoBuckets exactly,
			// "unknown" always last) so grouping this endpoint's rows by
			// bucket_id reproduces the buckets endpoint's own {bucket_ids,counts}
			// byte-for-byte, then the album's effective photo sort criterion as
			// intra-bucket tie-break.
			$query->orderByRaw('(photo_album.bucket_id IS NULL) ASC')
				->orderBy('photo_album.bucket_id', $direction);
		}
		// For a non-`Album` source there is no stored `bucket_id` to
		// pre-sort by - `buildResource()` computes it live per row below
		// instead, off the same effective-sort-ordered rows.
		(new SortingDecorator($query))->orderPhotosBy($sorting->column, $sorting->order)->applyOrdering();

		$rows = $query->select($select)->selectRaw(implode(', ', $selects_raw))->toBase()->get();

		return $this->buildResource($rows, $can_read_ratings, $user !== null, $thumb_infos_on, $tags_on, $blank_titles, $is_regular_album, $sorting, $album);
	}

	/**
	 * Adds the three `type`-filtered `size_variants` `LEFT JOIN`s the ratio
	 * resolution requires — deliberately does NOT reproduce
	 * {@see \App\Models\Photo::getAspectRatioAttribute()}'s video-forces-1
	 * special case: a video's real ratio wins whenever any of the three
	 * exists.
	 *
	 * @param FixedQueryBuilder<Photo> $query
	 */
	private function joinRatioSizeVariants(FixedQueryBuilder $query): void
	{
		$query->leftJoin('size_variants as sv_original', function (JoinClause $join): void {
			$join->on('sv_original.photo_id', '=', 'photos.id')->where('sv_original.type', '=', SizeVariantType::ORIGINAL->value);
		});
		$query->leftJoin('size_variants as sv_medium', function (JoinClause $join): void {
			$join->on('sv_medium.photo_id', '=', 'photos.id')->where('sv_medium.type', '=', SizeVariantType::MEDIUM->value);
		});
		$query->leftJoin('size_variants as sv_small', function (JoinClause $join): void {
			$join->on('sv_small.photo_id', '=', 'photos.id')->where('sv_small.type', '=', SizeVariantType::SMALL->value);
		});
	}

	/**
	 * One `GROUP_CONCAT`(`STRING_AGG` on pgsql)-then-split aggregation,
	 * joined once — not a per-row join.
	 *
	 * @param FixedQueryBuilder<Photo> $query
	 */
	private function joinTagAggregation(FixedQueryBuilder $query): void
	{
		$concat_expression = match (DB::getDriverName()) {
			'pgsql' => "string_agg(tags.name, ',')",
			default => 'group_concat(tags.name)',
		};

		$sub_query = 'select photos_tags.photo_id, ' . $concat_expression . ' as tag_names '
			. 'from photos_tags inner join tags on tags.id = photos_tags.tag_id '
			. 'group by photos_tags.photo_id';

		// FixedQueryBuilder::leftJoin() enforces a plain string $table, so
		// this derived-table join is added directly on the underlying query
		// builder instead (mirrors PhotoQueryPolicy::prepareModelQueryOrFail()'s
		// own `$query->getQuery()`-then-join pattern).
		$query->getQuery()->leftJoinSub($sub_query, 'tag_agg', 'tag_agg.photo_id', '=', 'photos.id');
	}

	/**
	 * @param \Illuminate\Support\Collection<int,\stdClass> $rows
	 */
	private function buildResource(
		\Illuminate\Support\Collection $rows,
		bool $can_read_ratings,
		bool $is_authenticated,
		bool $thumb_infos_on,
		bool $tags_on,
		bool $blank_titles,
		bool $is_regular_album,
		PhotoSortingCriterion $sorting,
		AbstractAlbum $album,
	): PhotoRatioResource {
		// Only used for a non-`Album` source (see below) - there is no
		// stored `bucket_id` to read off the row in that case, so it is
		// computed live per row instead, mirroring
		// `QueryPhotoBuckets::queryLiveBuckets()` exactly so both tiers
		// agree on the same bucket for the same photo. Harmless to resolve
		// unconditionally - cheap, and simply unused for a regular `Album`.
		$granularity = $this->bucket_computer->resolveGranularity($this->resolvePhotoTimeline($album));

		$ids = [];
		$titles = [];
		$types = [];
		$bucket_ids = [];
		$ratios = [];
		$owner_ids = [];
		$is_highlighteds = [];
		$is_validateds = [];
		$is_videos = [];
		$is_raws = [];
		$is_live_photos = [];
		$taken_ats = [];
		$created_ats = [];
		$taken_at_orig_tzs = [];
		$rating_avgs = [];
		$rating_users = [];
		$thumb_infos = [];
		$tags = [];

		foreach ($rows as $row) {
			$type = $row->type ?? '';
			$is_video = $type !== '' && $this->file_extension_service->isSupportedVideoMimeType($type);
			$is_photo = $type !== '' && $this->file_extension_service->isSupportedImageMimeType($type);

			$ids[] = $row->id;
			$titles[] = $blank_titles ? '' : $row->title;
			$types[] = $type;
			$bucket_ids[] = $is_regular_album
				? ($row->bucket_id ?? 'unknown')
				: ($this->liveBucketId($sorting->column, $granularity, $row) ?? 'unknown');
			$ratios[] = (float) $row->ratio;
			$owner_ids[] = (int) $row->owner_id;
			$is_highlighteds[] = DbBool::parse($row->is_highlighted);
			$is_validateds[] = DbBool::parse($row->is_validated);
			$is_videos[] = $is_video;
			$is_raws[] = !$is_photo && !$is_video;
			$is_live_photos[] = $row->live_photo_short_path !== null && $row->live_photo_short_path !== '';
			$taken_ats[] = $row->taken_at;
			$created_ats[] = $row->created_at;
			$taken_at_orig_tzs[] = $row->taken_at_orig_tz;

			if ($can_read_ratings) {
				$rating_avgs[] = $row->rating_avg !== null ? (float) $row->rating_avg : 0.0;
				if ($is_authenticated) {
					$rating_users[] = $row->rating_user !== null ? (int) $row->rating_user : null;
				}
			}

			if ($thumb_infos_on) {
				$description = $row->description ?? '';
				$thumb_infos[] = $description === '' ? null : Markdown::convert($description)->getContent();
			}

			if ($tags_on) {
				$tag_names = $row->tag_names ?? '';
				$tags[] = $tag_names === '' ? [] : explode(',', $tag_names);
			}
		}

		return new PhotoRatioResource(
			ids: $ids,
			titles: $titles,
			types: $types,
			bucket_ids: $bucket_ids,
			ratios: $ratios,
			owner_ids: $owner_ids,
			is_highlighteds: $is_highlighteds,
			is_validateds: $is_validateds,
			is_videos: $is_videos,
			is_raws: $is_raws,
			is_live_photos: $is_live_photos,
			taken_ats: $taken_ats,
			created_ats: $created_ats,
			taken_at_orig_tzs: $taken_at_orig_tzs,
			rating_avgs: $can_read_ratings ? $rating_avgs : Optional::create(),
			rating_users: ($can_read_ratings && $is_authenticated) ? $rating_users : Optional::create(),
			thumb_infos: $thumb_infos_on ? $thumb_infos : Optional::create(),
			tags: $tags_on ? $tags : Optional::create(),
		);
	}
}
