<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Feature 112 (FR-112-13): re-link sold size variants detached by mistake.
 *
 * Deleting photos used to set `photo_id = NULL` on every sold size variant of
 * the instance, not only on those of the deleted photos. A sold variant whose
 * order item still points at an existing photo (the FK clears
 * `order_items.photo_id` when the photo is deleted) is re-linked to it.
 *
 * Per (photo, type), the candidate with the highest id is re-linked, unless
 * the photo already has a variant of that type (`unique(photo_id, type)`):
 * then it stays detached, with its row and file kept.
 * A second run changes nothing.
 */
return new class() extends Migration {
	private const CHUNK_SIZE = 500;

	public function up(): void
	{
		// (photo_id, type) => highest detached sold size variant id.
		$candidates = [];
		$rows = DB::table('size_variants', 'sv')
			->join('order_items as oi', 'oi.size_variant_id', '=', 'sv.id')
			->join('photos as p', 'p.id', '=', 'oi.photo_id')
			->whereNull('sv.photo_id')
			->select(['sv.id', 'sv.type', 'oi.photo_id'])
			->orderBy('sv.id')
			->get();
		foreach ($rows as $row) {
			$candidates[$row->photo_id . '|' . $row->type] = ['id' => intval($row->id), 'photo_id' => $row->photo_id, 'type' => intval($row->type)];
		}

		$taken = $this->existingVariants(array_values(array_unique(array_column($candidates, 'photo_id'))));

		$relinked = 0;
		foreach ($candidates as $key => $candidate) {
			if (isset($taken[$key])) {
				continue;
			}
			$relinked += DB::table('size_variants')
				->where('id', '=', $candidate['id'])
				->whereNull('photo_id')
				->update(['photo_id' => $candidate['photo_id']]);
		}

		$detached = count(array_unique($rows->pluck('id')->all())) - $relinked;
		Log::notice(sprintf('Feature 112: re-linked %d sold size variants, %d left detached.', $relinked, $detached));
	}

	public function down(): void
	{
		// Data repair: nothing to undo.
	}

	/**
	 * (photo_id, type) pairs of the size variants attached to the given photos.
	 *
	 * @param string[] $photo_ids
	 *
	 * @return array<string,true>
	 */
	private function existingVariants(array $photo_ids): array
	{
		$taken = [];
		foreach (array_chunk($photo_ids, self::CHUNK_SIZE) as $chunk) {
			$rows = DB::table('size_variants')->whereIn('photo_id', $chunk)->select(['photo_id', 'type'])->get();
			foreach ($rows as $row) {
				$taken[$row->photo_id . '|' . $row->type] = true;
			}
		}

		return $taken;
	}
};
