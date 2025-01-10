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
	private const ALBUMS = 'albums';
	private const MIN = 'min_takestamp';
	private const MAX = 'max_takestamp';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table(self::ALBUMS, function (Blueprint $table) {
			$table->dropColumn(self::MIN);
		});
		Schema::table(self::ALBUMS, function (Blueprint $table) {
			$table->dropColumn(self::MAX);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table(self::ALBUMS, function ($table) {
			$table->timestamp(self::MIN)->nullable()->after('description');
		});
		Schema::table(self::ALBUMS, function ($table) {
			$table->timestamp(self::MAX)->nullable()->after(self::MIN);
		});

		$albums = DB::table('albums')
			->select(['id'])
			->addSelect([
				'min_takestamp' => DB::table('photos')
					->select('takestamp')
					->join('albums as a', 'a.id', '=', 'album_id')
					->whereColumn('a._lft', '>=', 'albums._lft')
					->whereColumn('a._rgt', '<=', 'albums._rgt')
					->whereNotNull('takestamp')
					->orderBy('takestamp', 'asc')
					->limit(1),
				'max_takestamp' => DB::table('photos')
					->select('takestamp')
					->join('albums as a', 'a.id', '=', 'album_id')
					->whereColumn('a._lft', '>=', 'albums._lft')
					->whereColumn('a._rgt', '<=', 'albums._rgt')
					->whereNotNull('takestamp')
					->orderBy('takestamp', 'desc')
					->limit(1),
			])
			->get();
		foreach ($albums as $album) {
			DB::table('albums')
				->where('id', '=', $album->id)
				->update([
					'min_takestamp' => $album->min_takestamp,
					'max_takestamp' => $album->max_takestamp,
				]);
		}
	}
};
