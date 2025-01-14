<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	private const TABLE_NAME = 'photos';
	private const ID_COL_NAME = 'id';
	private const OLD_COL_NAME = 'size';
	private const NEW_COL_NAME = 'filesize';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table(self::TABLE_NAME, function (Blueprint $table) {
			$table->unsignedBigInteger(self::NEW_COL_NAME)->default(0)->after(self::OLD_COL_NAME);
		});

		DB::beginTransaction();

		$photos = DB::table(self::TABLE_NAME)
			->select([self::ID_COL_NAME, self::OLD_COL_NAME])
			->lazyById();

		// We convert the approximated filesize which has already been stored in the database to covert it into the
		// "raw" filesize in bytes.
		// This does not yield 100% accurate results, but is more efficient than calling `filesize()` and performing
		// a real disk I/O for every single file for large setups with >50k photos.
		// Also, it is more user-friendly than just using the default value 0 for the new column.
		// The approximation error can easily be fixed by calling `artisan lychee:exif_lens` from the CLI.
		// This will replace the estimation by the actual filesize but takes time.
		foreach ($photos as $photo) {
			if (strpos($photo->size, 'MB') !== false) {
				$filesize = (int) (floatval(trim(str_replace('MB', '', $photo->size))) * 1024 * 1024);
			} elseif (strpos($photo->size, 'KB') !== false || strpos($photo->size, 'kB') !== false) {
				$filesize = (int) (floatval(trim(str_replace(['KB', 'kB'], '', $photo->size))) * 1024);
			} elseif (strpos($photo->size, 'B') !== false) {
				$filesize = (int) (floatval(trim(str_replace('B', '', $photo->size))));
			} else {
				$filesize = 0;
			}
			DB::table(self::TABLE_NAME)
				->where(self::ID_COL_NAME, '=', $photo->id)
				->update([self::NEW_COL_NAME => $filesize]);
		}

		DB::commit();

		Schema::table(self::TABLE_NAME, function (Blueprint $table) {
			$table->dropColumn(self::OLD_COL_NAME);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table(self::TABLE_NAME, function (Blueprint $table) {
			$table->string(self::OLD_COL_NAME, 20)->default('')->after(self::NEW_COL_NAME);
		});

		DB::beginTransaction();

		$photos = DB::table(self::TABLE_NAME)
			->select([self::ID_COL_NAME, self::NEW_COL_NAME])
			->lazyById();

		foreach ($photos as $photo) {
			if ($photo->filesize >= 1024 * 1024) {
				$size = round($photo->filesize / (1024 * 1024), 1) . ' MB';
			} elseif ($photo->filesize >= 1024) {
				$size = round($photo->filesize / 1024, 1) . ' KB';
			} else {
				$size = round($photo->filesize, 1) . ' B';
			}
			DB::table(self::TABLE_NAME)
				->where(self::ID_COL_NAME, '=', $photo->id)
				->update([self::OLD_COL_NAME => $size]);
		}

		DB::commit();

		Schema::table(self::TABLE_NAME, function (Blueprint $table) {
			$table->dropColumn(self::NEW_COL_NAME);
		});
	}
};
