<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Face;

use App\Http\Resources\V3\FaceQueueResource;
use App\Models\Face;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Builds the face quick naming queue (Feature 093, FR-093-02, FR-093-12).
 *
 * One SELECT (NFR-093-05): the cluster sizes come from a grouped derived
 * table and the suggested person from correlated subqueries, so the whole
 * queue is read in a single round-trip without hydrating models.
 */
class QueryFaceQueue
{
	/**
	 * @param User     $user          caller; admins see every face, others only faces on photos they own
	 * @param int|null $cluster_label restricts the queue to one cluster when set
	 * @param int      $limit         cap from `ai_vision_face_queue_limit` (ADR-069-01, strategy 3)
	 * @param int      $seen          faces of the session still queued; widens the window from the top (Q-093-14)
	 */
	public function do(User $user, ?int $cluster_label, int $limit, int $seen): FaceQueueResource
	{
		// Not clamped: the window never exceeds the caller's own pending faces (Q-093-15).
		$window = $limit + $seen;

		$owner_id = $user->may_administrate ? null : $user->id;

		$query = $this->pendingFaces(DB::table('faces'), 'faces', $owner_id)
			->leftJoinSub($this->clusterSizes($owner_id), 'cs', 'cs.cluster_label', '=', 'faces.cluster_label')
			->when($cluster_label !== null, fn (Builder $q) => $q->where('faces.cluster_label', '=', $cluster_label))
			->select(['faces.id', 'faces.photo_id', 'faces.x', 'faces.y', 'faces.width', 'faces.height', 'faces.crop_token', 'faces.cluster_label', 'faces.laplacian_variance'])
			->selectSub($this->suggestedPerson($owner_id, 'id'), 'suggested_person_id')
			->selectSub($this->suggestedPerson($owner_id, 'name'), 'suggested_person_name');

		$this->applyOrdering($query);

		$rows = $query->limit($window + 1)->get();

		return $this->toResource($rows->all(), $window);
	}

	/**
	 * Unassigned, non-dismissed faces, restricted to the owner's photos when
	 * `$owner_id` is set.
	 */
	private function pendingFaces(Builder $query, string $alias, ?int $owner_id): Builder
	{
		return $query
			->whereNull($alias . '.person_id')
			->where($alias . '.is_dismissed', '=', false)
			->when($owner_id !== null, fn (Builder $q) => $q
				->join('photos as ' . $alias . '_photos', $alias . '_photos.id', '=', $alias . '.photo_id')
				->where($alias . '_photos.owner_id', '=', $owner_id));
	}

	/**
	 * Size of each cluster over the same filtered set of faces.
	 */
	private function clusterSizes(?int $owner_id): Builder
	{
		return $this->pendingFaces(DB::table('faces as cf'), 'cf', $owner_id)
			->whereNotNull('cf.cluster_label')
			->where('cf.cluster_label', '>=', 0)
			->groupBy('cf.cluster_label')
			->select(['cf.cluster_label', DB::raw('COUNT(*) as cluster_size')]);
	}

	/**
	 * Correlated subquery: one column of the person behind the
	 * highest-confidence suggestion whose face is named and not dismissed,
	 * limited to persons the caller may see (NFR-093-07).
	 */
	private function suggestedPerson(?int $owner_id, string $column): Builder
	{
		return DB::table('face_suggestions as fs')
			->join('faces as sf', 'sf.id', '=', 'fs.suggested_face_id')
			->join('persons as sp', 'sp.id', '=', 'sf.person_id')
			->whereColumn('fs.face_id', 'faces.id')
			->where('sf.is_dismissed', '=', false)
			->when($owner_id !== null, fn (Builder $q) => $q->where(fn (Builder $w) => $w
				->where('sp.is_searchable', '=', true)
				->orWhere('sp.user_id', '=', $owner_id)))
			->orderByDesc('fs.confidence')
			->orderBy('sp.id')
			->limit(1)
			->select('sp.' . $column);
	}

	/**
	 * Clustered faces first (largest cluster first, then by label), then
	 * unclustered faces (`cluster_label` null or negative); confidence
	 * descending within each group. Explicit CASE/COALESCE keys keep the
	 * order identical on SQLite, MySQL and PostgreSQL (NULL ordering differs).
	 */
	private function applyOrdering(Builder $query): void
	{
		$unclustered = '(faces.cluster_label IS NULL OR faces.cluster_label < 0)';

		$query
			->orderByRaw('CASE WHEN ' . $unclustered . ' THEN 1 ELSE 0 END')
			->orderByRaw('COALESCE(cs.cluster_size, 0) DESC')
			->orderByRaw('CASE WHEN ' . $unclustered . ' THEN -1 ELSE faces.cluster_label END')
			->orderByDesc('faces.confidence')
			->orderBy('faces.id');
	}

	/**
	 * @param object[] $rows
	 */
	private function toResource(array $rows, int $limit): FaceQueueResource
	{
		$is_truncated = count($rows) > $limit;
		$rows = array_slice($rows, 0, $limit);

		$ids = [];
		$photo_ids = [];
		$xs = [];
		$ys = [];
		$widths = [];
		$heights = [];
		$crop_urls = [];
		$cluster_labels = [];
		$suggested_person_ids = [];
		$suggested_person_names = [];
		$laplacian_variances = [];
		foreach ($rows as $row) {
			$ids[] = $row->id;
			$photo_ids[] = $row->photo_id;
			$xs[] = (float) $row->x;
			$ys[] = (float) $row->y;
			$widths[] = (float) $row->width;
			$heights[] = (float) $row->height;
			$crop_urls[] = Face::cropUrlFromToken($row->crop_token);
			$cluster_labels[] = $row->cluster_label !== null ? (int) $row->cluster_label : null;
			$suggested_person_ids[] = $row->suggested_person_id !== null ? (string) $row->suggested_person_id : null;
			$suggested_person_names[] = $row->suggested_person_name;
			$laplacian_variances[] = (float) $row->laplacian_variance;
		}

		return new FaceQueueResource(
			ids: $ids,
			photo_ids: $photo_ids,
			xs: $xs,
			ys: $ys,
			widths: $widths,
			heights: $heights,
			crop_urls: $crop_urls,
			cluster_labels: $cluster_labels,
			suggested_person_ids: $suggested_person_ids,
			suggested_person_names: $suggested_person_names,
			laplacian_variances: $laplacian_variances,
			is_truncated: $is_truncated,
		);
	}
}
