<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\StructOfArrays;

use App\Assets\DbBool;
use App\Contracts\Models\AbstractAlbum;
use App\Eloquent\FixedQueryBuilder;
use App\Enum\PhotoThumbInfoType;
use App\Enum\VisibilityType;
use App\Http\Resources\V3\PhotoRatioResource;
use App\Models\Photo;
use App\Models\User;
use App\Repositories\ConfigManager;
use App\Services\BucketScheme;
use App\Services\Image\FileExtensionService;
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
	use JoinsRatioSizeVariants;
	use ResolvesPhotoSource;

	public function __construct(
		private readonly ConfigManager $config_manager,
		private readonly FileExtensionService $file_extension_service,
	) {
	}

	/**
	 * @param string[]|null $bucket_ids mutually exclusive with `$photo_ids`/`$limit` -
	 *                                  both `null` preserves today's
	 *                                  whole-scope behaviour byte-for-byte
	 *                                  (NFR-066-03)
	 * @param string[]|null $photo_ids  mutually exclusive with `$bucket_ids`/`$limit`
	 * @param int|null      $limit      mutually exclusive with `$bucket_ids`/`$photo_ids`
	 *                                  (Feature 068, FR-068-03) - caps the
	 *                                  returned rows to the first `$limit` in
	 *                                  the already-applied effective sort
	 *                                  order; `null` is a no-op
	 */
	public function do(AbstractAlbum $album, ?User $user, ?array $bucket_ids = null, ?array $photo_ids = null, ?int $limit = null): PhotoRatioResource
	{
		$sorting = $this->resolveEffectiveSorting($album);
		$scheme = $this->resolveScheme($album, $sorting);

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

		$query = $this->resolvePhotoQuery($album, $user, fn (FixedQueryBuilder $q) => $this->applyScopeFilter($q, $album, $scheme, $bucket_ids, $photo_ids));
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
			'photos.is_360',
			'photos.rating_avg',
		];
		$selects_raw = [self::RATIO_SELECT_RAW];
		if ($scheme->isBucketable()) {
			// Per-row id from the same expression the `buckets` tier groups
			// on (FR-092-06).
			$selects_raw[] = $this->bucketIdSql($album, $scheme) . ' as bucket_id';
		}

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

		$this->orderByBucket($query, $album, $scheme, $sorting);

		if ($limit !== null) {
			$query->limit($limit);
		}

		$rows = $query->select($select)->selectRaw(implode(', ', $selects_raw))->toBase()->get();

		return $this->buildResource($rows, $can_read_ratings, $user !== null, $thumb_infos_on, $tags_on, $blank_titles, $scheme);
	}

	/**
	 * Applies the optional, mutually exclusive `bucket_ids[]`/`photo_ids[]`
	 * window (FR-066-06) in SQL for every source (FR-092-07) - both `null`
	 * is a no-op (whole scope).
	 *
	 * @param FixedQueryBuilder<Photo> $query
	 * @param string[]|null            $bucket_ids
	 * @param string[]|null            $photo_ids
	 */
	private function applyScopeFilter(FixedQueryBuilder $query, AbstractAlbum $album, BucketScheme $scheme, ?array $bucket_ids, ?array $photo_ids): void
	{
		if ($photo_ids !== null) {
			$query->whereIn('photos.id', $photo_ids);

			return;
		}

		if ($bucket_ids !== null) {
			$this->applyBucketWindow($query, $album, $scheme, $bucket_ids);
		}
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
		BucketScheme $scheme,
	): PhotoRatioResource {
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
		$is_360s = [];
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
			$bucket_ids[] = $scheme->normalizeId($row->bucket_id ?? null);
			$ratios[] = (float) $row->ratio;
			$owner_ids[] = (int) $row->owner_id;
			$is_highlighteds[] = DbBool::parse($row->is_highlighted);
			$is_validateds[] = DbBool::parse($row->is_validated);
			$is_videos[] = $is_video;
			$is_raws[] = !$is_photo && !$is_video;
			$is_live_photos[] = $row->live_photo_short_path !== null && $row->live_photo_short_path !== '';
			$is_360s[] = DbBool::parse($row->is_360);
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
			is_360s: $is_360s,
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
