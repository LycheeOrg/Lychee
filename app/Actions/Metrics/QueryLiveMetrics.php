<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Metrics;

use App\Enum\MetricsAction;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Http\Resources\V3\LiveMetricsListResource;
use App\Models\User;
use App\Repositories\ConfigManager;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use function Safe\strtotime;

/**
 * Builds the `GET /api/v3/Metrics` feed (Feature 079) from one SELECT.
 *
 * The inner query groups the non-expired events per
 * `(action, album_id, photo_id, minute)` on `live_metrics` alone; the outer
 * query joins the titles, covers and owners onto those groups only. Same row
 * selection as {@see GetMetrics}: photo visits are left out, and the inner
 * join on `albums` drops tag albums.
 *
 * @phpstan-type TRow object{last_at:string,action:string,album_id:string,photo_id:?string,num_events:int|string,photo_title:?string,album_title:string,cover_id:?string,auto_cover_id_max_privilege:?string}
 */
class QueryLiveMetrics
{
	public function __construct(
		protected readonly ConfigManager $config_manager,
		protected readonly CleanupMetrics $cleanup_metrics,
	) {
	}

	public function do(User $user): LiveMetricsListResource
	{
		$limit = $this->config_manager->getValueAsInt('live_metrics_result_limit');

		$rows = DB::query()
			->fromSub($this->groupedEvents(), 'lm')
			->join('albums', 'albums.id', '=', 'lm.album_id')
			->join('base_albums', 'base_albums.id', '=', 'lm.album_id')
			->leftJoin('photos', 'photos.id', '=', 'lm.photo_id')
			->when(!$user->may_administrate, fn (Builder $q) => $q
				->where(fn (Builder $q2) => $q2
					->where('base_albums.owner_id', '=', $user->id)
					->orWhere('photos.owner_id', '=', $user->id)))
			->select([
				'lm.last_at',
				'lm.action',
				'lm.album_id',
				'lm.photo_id',
				'lm.num_events',
				'photos.title as photo_title',
				'base_albums.title as album_title',
				'albums.cover_id',
				'albums.auto_cover_id_max_privilege',
			])
			->orderBy('lm.last_at', 'desc')
			->limit($limit + 1)
			->get();

		$is_truncated = $rows->count() > $limit;

		return $this->toResource($is_truncated ? $rows->take($limit) : $rows, $is_truncated);
	}

	/**
	 * The last event time is aliased `last_at`, not `created_at`: MySQL
	 * resolves GROUP BY names against SELECT aliases first.
	 */
	private function groupedEvents(): Builder
	{
		return DB::table('live_metrics')
			->select(['live_metrics.action', 'live_metrics.album_id', 'live_metrics.photo_id'])
			->selectRaw('MAX(live_metrics.created_at) as last_at')
			->selectRaw('COUNT(*) as num_events')
			->where('live_metrics.created_at', '>', $this->cleanup_metrics->threshold())
			->where(fn (Builder $q) => $q
				->where('live_metrics.action', '!=', MetricsAction::VISIT->value)
				->orWhereNull('live_metrics.photo_id'))
			->groupBy(['live_metrics.action', 'live_metrics.album_id', 'live_metrics.photo_id'])
			->groupByRaw(self::minuteExpression('live_metrics.created_at'));
	}

	/**
	 * Truncates a datetime column to the minute, per driver
	 * (same pattern as {@see \App\Actions\Photo\StructOfArrays\QueryPhotoBuckets}).
	 */
	private static function minuteExpression(string $column): string
	{
		return match (DB::getDriverName()) {
			'sqlite' => "strftime('%Y-%m-%d %H:%M', " . $column . ')',
			'mysql', 'mariadb' => 'DATE_FORMAT(' . $column . ", '%Y-%m-%d %H:%i')",
			'pgsql' => 'to_char(' . $column . ", 'YYYY-MM-DD HH24:MI')",
			default => throw new LycheeInvalidArgumentException('Unsupported database driver'),
		};
	}

	/**
	 * @param Collection<int,TRow> $rows
	 */
	private function toResource(Collection $rows, bool $is_truncated): LiveMetricsListResource
	{
		$created_ats = [];
		$actions = [];
		$album_ids = [];
		$photo_ids = [];
		$titles = [];
		$thumb_photo_ids = [];
		$counts = [];

		foreach ($rows as $row) {
			$created_ats[] = date('c', strtotime($row->last_at));
			$actions[] = MetricsAction::from($row->action);
			$album_ids[] = $row->album_id;
			$photo_ids[] = $row->photo_id;
			// Chosen by event type, not by nullability: an untitled photo must not
			// fall back to the title of an album the photo owner may not access.
			$titles[] = $row->photo_id !== null ? ($row->photo_title ?? '') : $row->album_title;
			$thumb_photo_ids[] = $row->photo_id ?? $row->cover_id ?? $row->auto_cover_id_max_privilege;
			$counts[] = (int) $row->num_events;
		}

		return new LiveMetricsListResource(
			created_ats: $created_ats,
			actions: $actions,
			album_ids: $album_ids,
			photo_ids: $photo_ids,
			titles: $titles,
			thumb_photo_ids: $thumb_photo_ids,
			counts: $counts,
			is_truncated: $is_truncated,
		);
	}
}
