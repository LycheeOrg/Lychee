<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\SizeVariant;

use App\Actions\Shared\DispatchAfterDelete;
use App\Constants\QueryLimits;
use App\Enum\StorageDiskType;
use App\Exceptions\Internal\LycheeAssertionError;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\ModelDBException;
use App\Jobs\FileDeleterJob;
use App\Models\SizeVariant;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Deletes the size variants with the designated IDs **efficiently**.
 *
 * This class deliberately violates the principle of separations of concerns.
 * In an ideal world, the method would simply call `->delete()` on every
 * `SizeVariant` model and the `SizeVariant` model would take care of deleting
 * its associated media files.
 * But this is extremely inefficient due to Laravel's architecture:
 *
 *  - Models are heavyweight god classes such that every instance also carries
 *    the whole code for serialization/deserialization
 *  - Models are active records (and don't use the unit-of-work pattern), i.e.
 *    every deletion of a model directly triggers a DB operation; they are
 *    not deferred into a batch operation
 *
 * Moreover, while removing the records for size variants from the DB can be
 * implemented rather efficiently, the actual file operations may take some
 * time.
 * Especially, if the files are not stored locally but on a remote file system.
 * Hence, this method collects all files which need to be removed.
 * The caller can then decide to delete them asynchronously.
 */
class Delete
{
	/**
	 * Deletes the designated size variants from the DB.
	 *
	 * The method only deletes the records for size variants.
	 * The method does not delete the associated files from the physical storage.
	 * Instead, the method returns an object in which all these files have been collected.
	 * This object can (and must) be used to eventually delete the files,
	 * however doing so can be deferred.
	 *
	 * @param int[] $sv_ids the size variant IDs
	 *
	 * @return void
	 *
	 * @throws ModelDBException
	 */
	public function do(array $sv_ids): void
	{
		if (count($sv_ids) === 0) {
			return;
		}

		try {
			$jobs = [];
			foreach (array_chunk(array_values(array_unique($sv_ids)), QueryLimits::ID_CHUNK_SIZE) as $chunk) {
				array_push($jobs, ...$this->deleteChunk($chunk));
			}

			// After the response on `sync` (FR-112-04).
			DispatchAfterDelete::dispatch($jobs);

			// @codeCoverageIgnoreStart
		} catch (QueryBuilderException $e) {
			throw ModelDBException::create('size variants', 'deleting', $e);
		} catch (\InvalidArgumentException $e) {
			throw LycheeAssertionError::createFromUnexpectedException($e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Deletes one chunk of size variants and returns the jobs deleting their files.
	 *
	 * Sold variants (referenced by an order item) keep their row and their
	 * file but are detached from their photo, so that the photo can get a new
	 * variant of that type (FR-112-10).
	 *
	 * @param int[] $sv_ids
	 *
	 * @return list<FileDeleterJob>
	 */
	private function deleteChunk(array $sv_ids): array
	{
		// Variants and watermarked variants of one disk are merged, then
		// split into bounded jobs (FR-112-03).
		$jobs = [];
		foreach (StorageDiskType::cases() as $disk) {
			$size_variants = SizeVariant::query()
				->from('size_variants as sv')
				->select(['sv.short_path', 'sv.short_path_watermarked'])
				->join('photos as p', 'p.id', '=', 'sv.photo_id')
				->where('sv.storage_disk', '=', $disk->value)
				->whereIn('sv.id', $sv_ids)
				->whereNotExists(fn (Builder $query) => self::orderItemOf($query, 'sv.id'))
				->toBase()
				->get();
			$paths = array_merge(
				$size_variants->pluck('short_path')->filter()->all(),
				$size_variants->pluck('short_path_watermarked')->filter()->all(),
			);
			array_push($jobs, ...FileDeleterJob::batches($disk, $paths));
		}

		DB::table('size_variants')
			->whereIn('id', $sv_ids)
			->whereExists(fn (Builder $query) => self::orderItemOf($query, 'size_variants.id'))
			->update(['photo_id' => null]);

		DB::table('size_variants')
			->whereIn('id', $sv_ids)
			->whereNotExists(fn (Builder $query) => self::orderItemOf($query, 'size_variants.id'))
			->delete();

		return $jobs;
	}

	/**
	 * Subquery matching the order items that reference a size variant.
	 *
	 * NULL-safe, unlike `NOT IN (list of order_items.size_variant_id)`:
	 * order items delivered by URL have no size variant (FR-112-10).
	 *
	 * @param Builder $query
	 * @param string  $size_variant_id_column qualified size variant id column of the outer query
	 */
	public static function orderItemOf(Builder $query, string $size_variant_id_column): Builder
	{
		return $query->select(DB::raw(1))
			->from('order_items as oi')
			->whereColumn('oi.size_variant_id', '=', $size_variant_id_column);
	}
}
