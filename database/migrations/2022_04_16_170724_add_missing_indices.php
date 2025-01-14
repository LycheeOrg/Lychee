<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Doctrine\DBAL\Exception as DBALException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require_once 'TemporaryModels/OptimizeTables.php';

return new class() extends Migration {
	private string $driverName;
	private OptimizeTables $optimize;

	/**
	 * @throws DBALException
	 */
	public function __construct()
	{
		$connection = Schema::connection(null)->getConnection();
		$this->driverName = $connection->getDriverName();
		$this->optimize = new OptimizeTables();
	}

	public function up(): void
	{
		Schema::table('photos', function (Blueprint $table) {
			// These indices are needed to efficiently retrieve the covers of
			// albums acc. to different sorting criteria
			// Note, that covers are always sorted acc. to `is_starred` first.
			$table->index(['album_id', 'is_starred', 'title']);

			// In the case of mysql we apply the RAW query below.
			if ($this->driverName !== 'mysql') {
				$table->index(['album_id', 'is_starred', 'description']);
			}
		});

		// MySQL cannot create indices over unlimited string values
		// So we must explicitly define an upper bound on how many characters
		// are analyzed for sorting
		if ($this->driverName === 'mysql') {
			DB::statement('alter table `photos` add index `photos_album_id_is_starred_description(128)_index`(album_id, is_starred, description(128))');
		}
	}

	public function down(): void
	{
		$descriptionSQL = match ($this->driverName) {
			'mysql' => 'description(128)',
			default => 'description',
		};

		Schema::table('photos', function (Blueprint $table) use ($descriptionSQL) {
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_title_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_' . $descriptionSQL . '_index');
		});
	}
};
