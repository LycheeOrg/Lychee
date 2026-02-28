<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\QueryException;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::transaction(function (): void {
			$this->refactorPhotosAvoidDuplicates();
		});
	}

	/**
	 * Refactor the photos to avoid duplicates.
	 */
	private function refactorPhotosAvoidDuplicates(): void
	{
		$result = DB::table('photos')
			->leftJoin('photos as dup', function (JoinClause $join): void {
				$join->on('dup.checksum', '=', 'photos.checksum');
			})
			->select('photos.checksum')
			->groupBy('photos.checksum')
			->having(DB::raw('count(photos.id)'), '>', 1)
			->pluck('checksum');

		foreach ($result as $checksum) {
			$photos_ids = DB::table('photos')->select('id')->where('checksum', $checksum)->pluck('id')->all();
			if (count($photos_ids) < 2) {
				// This should not happen but just in case...
				continue;
			}
			$photo_to_keep = array_shift($photos_ids);
			// Check if there are any references to those photos in photo_album, statistics, purchasables
			$album_ids = DB::table('photo_album')->where('photo_id', $photo_to_keep)->select('album_id')->pluck('album_id')->all();

			// We have to use a foreach here because we want to catch the exceptions individually
			foreach ($photos_ids as $photo_id) {
				// Begin an inner transaction to avoid aborting when updates fail
				DB::beginTransaction();
				try {
					DB::table('photo_album')->where('photo_id', '=', $photo_id)->whereNotIn('album_id', $album_ids)->update(['photo_id' => $photo_to_keep]);
					// update the link
					DB::commit();
				} catch (QueryException $e) {
					// If this crashes this means that the link already exists between the photo and the album.
					// We ignore it.
					// So we rollback this part
					DB::rollBack();
				} catch (\Exception $e) {
					throw $e;
				}

				// Same as before, we guard against failing updates with an inner transaction
				DB::beginTransaction();
				try {
					DB::table('purchasables')->where('photo_id', '=', $photo_id)->whereNotIn('album_id', $album_ids)->update(['photo_id' => $photo_to_keep]);
					DB::commit();
				} catch (QueryException $e) {
					// Same shit as above. :)
					DB::rollBack();
				} catch (\Exception $e) {
					throw $e;
				}
			}

			// Delete the remaining links
			DB::table('size_variants')->whereIn('photo_id', $photos_ids)->delete();
			DB::table('purchasables')->whereIn('photo_id', $photos_ids)->delete();
			DB::table('statistics')->whereIn('photo_id', $photos_ids)->delete();
			DB::table('photo_album')->whereIn('photo_id', $photos_ids)->delete();
			DB::table('photos')->whereIn('id', $photos_ids)->delete();
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// There is no coming back sorry.
	}
};
