<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\SizeVariant;

use App\Enum\StorageDiskType;
use App\Exceptions\Internal\LycheeAssertionError;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\ModelDBException;
use App\Jobs\FileDeleterJob;
use App\Models\SizeVariant;
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
		try {
			// Maybe consider doing multiple queries for the different storage types.
			$exclude_ids = DB::table('order_items')->select(['size_variant_id'])->pluck('size_variant_id')->all();

			// Maybe consider doing multiple queries for the different storage types.
			$size_variants_local = SizeVariant::query()
				->from('size_variants as sv')
				->select(['sv.short_path', 'sv.short_path_watermarked'])
				->join('photos as p', 'p.id', '=', 'sv.photo_id')
				->where('sv.storage_disk', '=', StorageDiskType::LOCAL->value)
				->whereNotIn('sv.id', $exclude_ids)
				->toBase()
				->get();

			$size_variants_s3 = SizeVariant::query()
				->from('size_variants as sv')
				->select(['sv.short_path', 'sv.short_path_watermarked'])
				->join('photos as p', 'p.id', '=', 'sv.photo_id')
				->where('sv.storage_disk', '=', StorageDiskType::S3->value)
				->whereNotIn('sv.id', $exclude_ids)
				->toBase()
				->get();

			$jobs = [];
			$jobs[] = new FileDeleterJob(StorageDiskType::LOCAL, $size_variants_local->pluck('short_path')->all());
			$jobs[] = new FileDeleterJob(StorageDiskType::S3, $size_variants_s3->pluck('short_path')->all());

			SizeVariant::query()
				->whereIn('id', $sv_ids)
				->whereNotIn('id', $exclude_ids)
				->delete();

			foreach ($jobs as $job) {
				dispatch($job);
			}

			// @codeCoverageIgnoreStart
		} catch (QueryBuilderException $e) {
			throw ModelDBException::create('size variants', 'deleting', $e);
		} catch (\InvalidArgumentException $e) {
			throw LycheeAssertionError::createFromUnexpectedException($e);
		}
		// @codeCoverageIgnoreEnd
	}
}