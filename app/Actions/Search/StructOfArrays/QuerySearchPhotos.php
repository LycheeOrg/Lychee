<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Search\StructOfArrays;

use App\Actions\Photo\StructOfArrays\JoinsRatioSizeVariants;
use App\Assets\DbBool;
use App\DTO\PhotoSortingCriterion;
use App\DTO\Search\SearchToken;
use App\Eloquent\FixedQueryBuilder;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Enum\PhotoThumbInfoType;
use App\Enum\SmartAlbumType;
use App\Enum\VisibilityType;
use App\Http\Resources\V3\SearchPhotoResource;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use App\Models\Photo;
use App\Models\User;
use App\Repositories\ConfigManager;
use App\Services\Image\FileExtensionService;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;

/**
 * Query logic for `GET /api/v3/Search/Photos` (Feature 069, FR-069-01).
 *
 * `toBase()`-only, one flat query — no Eloquent hydration, no relation
 * eager-loading, and every display gate resolved once per request rather than
 * per photo (FR-069-14, NFR-069-02/03). Deliberately has **no bucket tier**
 * (spec.md NG1), which makes it materially simpler than its per-album
 * counterpart {@see \App\Actions\Photo\StructOfArrays\QueryPhotoRatios}: no
 * stored-vs-live `bucket_id` split, no bucket ordering, no bucket windowing.
 *
 * In exchange it carries the one thing that counterpart never needs — a bound.
 * Search scope has no structural ceiling, so the query fetches `limit + 1` rows
 * and reports {@see SearchPhotoResource::$is_truncated} when the extra row
 * exists (FR-069-02, ADR-0010).
 */
class QuerySearchPhotos
{
	use JoinsRatioSizeVariants;

	public function __construct(
		private readonly SearchPhotoSource $source,
		private readonly ConfigManager $config_manager,
		private readonly FileExtensionService $file_extension_service,
	) {
	}

	/**
	 * @param array<int,SearchToken> $tokens
	 */
	public function do(array $tokens, ?Album $origin, ?User $user, ?PhotoSortingCriterion $sorting): SearchPhotoResource
	{
		$sorting ??= self::defaultSorting();

		$rating_enabled = $this->config_manager->getValueAsBool('rating_enabled');
		// PhotoPolicy::canReadRatings() ignores its $photo argument entirely,
		// so this is safe to evaluate once per request rather than per photo.
		$can_read_ratings = $rating_enabled && ($user !== null || $this->config_manager->getValueAsBool('rating_public'));
		$is_authenticated = $user !== null;

		$overlay = $this->config_manager->getValueAsEnum('display_thumb_photo_overlay', VisibilityType::class) ?? VisibilityType::NEVER;
		$thumb_info_mode = $this->config_manager->getValueAsEnum('photo_thumb_info', PhotoThumbInfoType::class) ?? PhotoThumbInfoType::TITLE;
		$thumb_infos_on = $overlay !== VisibilityType::NEVER && $thumb_info_mode === PhotoThumbInfoType::DESCRIPTION;
		$tags_on = $overlay !== VisibilityType::NEVER && $thumb_info_mode === PhotoThumbInfoType::TITLE && $this->config_manager->getValueAsBool('photo_thumb_tags_enabled');
		$blank_titles = $this->config_manager->getValueAsBool('file_name_hidden') && $user === null;

		$limit = max(1, $this->config_manager->getValueAsInt('search_result_limit'));

		$query = $this->source->query($tokens, $origin);
		$this->joinRatioSizeVariants($query);

		$select = [
			'photos.id',
			'photos.title',
			'photos.type',
			'photos.owner_id',
			'photos.is_highlighted',
			'photos.is_validated',
			'photos.taken_at',
			'photos.created_at',
			'photos.taken_at_orig_tz',
			'photos.live_photo_short_path',
			'photos.rating_avg',
		];

		if ($can_read_ratings && $is_authenticated) {
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

		// Ordering must be applied before the limit, or `is_truncated` would
		// report on an arbitrary slice rather than on the head of the result
		// (plan R3). `orderPhotosBy()` qualifies the columns with `photos.` —
		// required, not cosmetic: `title_base`/`title_index`/`created_at` exist
		// on `base_albums` too, and the policy's joins bring that table into
		// scope, so an unqualified ORDER BY is ambiguous (plan R2).
		(new SortingDecorator($query))->orderPhotosBy($sorting->column, $sorting->order)->applyOrdering();

		// One row over the cap: its presence is the truncation signal, and it
		// is discarded rather than returned.
		$rows = $query->select($select)
			->selectRaw(self::RATIO_SELECT_RAW)
			->limit($limit + 1)
			->toBase()
			->get();

		$is_truncated = $rows->count() > $limit;
		if ($is_truncated) {
			$rows = $rows->take($limit);
		}

		$photo_ids = $rows->pluck('id')->all();
		$album_ids_by_photo_id = $this->source->resolveAlbumIds($photo_ids, $origin, $user);

		return $this->buildResource(
			$rows,
			$album_ids_by_photo_id,
			$is_truncated,
			$can_read_ratings,
			$is_authenticated,
			$thumb_infos_on,
			$tags_on,
			$blank_titles,
		);
	}

	/**
	 * Reproduces v2 `SearchController::search()`'s own fallback exactly — a
	 * hard-coded `taken_at ASC`, deliberately *not*
	 * {@see PhotoSortingCriterion::createDefault()}'s config-driven value
	 * (NFR-069-06).
	 */
	public static function defaultSorting(): PhotoSortingCriterion
	{
		return new PhotoSortingCriterion(ColumnSortingType::TAKEN_AT, OrderSortingType::ASC);
	}

	/**
	 * One `GROUP_CONCAT`(`STRING_AGG` on pgsql)-then-split aggregation, joined
	 * once — not a per-row join. Mirrors `QueryPhotoRatios`' own helper; kept
	 * local rather than shared because the two differ in nothing but context
	 * and sharing it would pull an album-shaped trait into a non-album tier.
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

		$query->getQuery()->leftJoinSub($sub_query, 'tag_agg', 'tag_agg.photo_id', '=', 'photos.id');
	}

	/**
	 * @param \Illuminate\Support\Collection<int,\stdClass> $rows
	 * @param array<string,string>                          $album_ids_by_photo_id
	 */
	private function buildResource(
		\Illuminate\Support\Collection $rows,
		array $album_ids_by_photo_id,
		bool $is_truncated,
		bool $can_read_ratings,
		bool $is_authenticated,
		bool $thumb_infos_on,
		bool $tags_on,
		bool $blank_titles,
	): SearchPhotoResource {
		$ids = [];
		$album_ids = [];
		$titles = [];
		$types = [];
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
			// A photo the viewer owns but which sits in no album at all is
			// searchable in v2, so it must stay searchable here (NFR-069-06).
			// It has no containing album to name, so it reports the `unsorted`
			// smart album — where the UI would navigate to find it anyway, and
			// which the v3 Asset endpoint already accepts (Q-069-11).
			$album_ids[] = $album_ids_by_photo_id[$row->id] ?? SmartAlbumType::UNSORTED->value;
			$titles[] = $blank_titles ? '' : $row->title;
			$types[] = $type;
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

		return new SearchPhotoResource(
			ids: $ids,
			album_ids: $album_ids,
			titles: $titles,
			types: $types,
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
			is_truncated: $is_truncated,
			rating_avgs: $can_read_ratings ? $rating_avgs : Optional::create(),
			rating_users: ($can_read_ratings && $is_authenticated) ? $rating_users : Optional::create(),
			thumb_infos: $thumb_infos_on ? $thumb_infos : Optional::create(),
			tags: $tags_on ? $tags : Optional::create(),
		);
	}
}
