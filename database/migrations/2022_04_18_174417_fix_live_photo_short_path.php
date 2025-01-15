<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Doctrine\DBAL\Exception as DBALException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	private string $driverName;

	/**
	 * @throws DBALException
	 */
	public function __construct()
	{
		$connection = Schema::connection(null)->getConnection();
		$this->driverName = $connection->getDriverName();
	}

	/**
	 * Run the migrations.
	 *
	 * @throws RuntimeException
	 */
	public function up(): void
	{
		// MySQL misuses the ANSI SQL concatenation operator `||` for
		// a logical OR and provides the proprietary `CONCAT` statement
		// instead.
		$sqlConcatLivePhotoPath = match ($this->driverName) {
			'mysql' => DB::raw('CONCAT(\'big/\', live_photo_short_path)'),
			'pgsql', 'sqlite' => DB::raw('\'big/\' || live_photo_short_path'),
			default => throw new \RuntimeException('Unknown DBMS'),
		};

		DB::table('photos')
			->whereNotNull('live_photo_short_path')
			->where('live_photo_short_path', 'not like', '%/%')
			->update(['live_photo_short_path' => $sqlConcatLivePhotoPath]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @throws RuntimeException
	 */
	public function down(): void
	{
		// In contrast to all other programming languages, the first character
		// of a string has index 1 (not 0) in SQL.
		// We want to remove `'big/'` or `'raw/'` from the string.
		$sqlSubstringLivePhotoPath = match ($this->driverName) {
			'mysql', 'pgsql' => DB::raw('SUBSTRING(live_photo_short_path FROM 5)'),
			'sqlite' => DB::raw('SUBSTR(live_photo_short_path, 5)'),
			default => throw new \RuntimeException('Unknown DBMS'),
		};

		DB::table('photos')
			->whereNotNull('live_photo_short_path')
			->where('live_photo_short_path', 'like', '%/%')
			->update(['live_photo_short_path' => $sqlSubstringLivePhotoPath]);
	}
};
