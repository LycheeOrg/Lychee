<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Facades\Helpers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use function Safe\date;
use Safe\Exceptions\DatetimeException;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		if (DB::table('albums')->count('id') === 0) {
			if (Schema::hasTable(env('DB_OLD_LYCHEE_PREFIX', '') . 'lychee_albums')) {
				$results = DB::table(env('DB_OLD_LYCHEE_PREFIX', '') . 'lychee_albums')->select('*')->orderBy('id', 'asc')->get();
				$id = 0;
				foreach ($results as $result) {
					$id = Helpers::trancateIf32($result->id, (int) $id);
					try {
						$date = date('Y-m-d H:i:s', $result->sysstamp);
					} catch (DatetimeException) {
						$date = date('Y-m-d H:i:s');
					}

					DB::table('albums')->insert([
						'id' => $id,
						'title' => $result->title,
						'description' => $result->description,
						'public' => $result->public,
						'visible_hidden' => $result->visible,
						'password' => $result->password,
						'license' => $result->license ?? 'none',
						'created_at' => $date,
					]);
				}
			} else {
				Log::notice(__METHOD__ . ':' . __LINE__ . ' ' . env('DB_OLD_LYCHEE_PREFIX', '') . 'lychee_albums does not exist!');
			}
		} else {
			Log::notice(__METHOD__ . ':' . __LINE__ . ' albums is not empty.');
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if (Schema::hasTable('lychee_albums')) {
			Schema::disableForeignKeyConstraints();
			DB::table('albums')->truncate();
			Schema::enableForeignKeyConstraints();
		}
	}
};
