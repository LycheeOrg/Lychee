<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Enum\StorageDiskType;
use App\Jobs\FileDeleterJob;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

return new class() extends Migration {
	private ConsoleOutput $output;
	private ConsoleSectionOutput $msg_section;

	public function __construct()
	{
		$this->output = new ConsoleOutput();
		$this->msg_section = $this->output->section();
	}

	public const SHORT_PATH_WATERMARKED = 'short_path_watermarked';

	/**
	 * Run the migrations.
	 */
	final public function up(): void
	{
		// Sadly we need to delete all the files associated with the path
		if (!Schema::hasColumn('size_variants', self::SHORT_PATH_WATERMARKED)) {
			Schema::table('size_variants', function (Blueprint $table) {
				$table->string(self::SHORT_PATH_WATERMARKED, 255)->nullable()->default(null)->after('short_path');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @codeCoverageIgnore Tested but after CI run...
	 */
	final public function down(): void
	{
		$files_to_delete_local = DB::table('size_variants')
			->select(self::SHORT_PATH_WATERMARKED)
			->whereNotNull(self::SHORT_PATH_WATERMARKED)
			->where('storage_disk', '=', StorageDiskType::LOCAL->value)
			->pluck('short_path_watermarked');

		$files_to_delete_s3 = DB::table('size_variants')
			->select(self::SHORT_PATH_WATERMARKED)
			->whereNotNull(self::SHORT_PATH_WATERMARKED)
			->where('storage_disk', '=', StorageDiskType::S3->value)
			->pluck('short_path_watermarked');

		try {
			if (!$files_to_delete_local->isEmpty()) {
				$job = new FileDeleterJob(StorageDiskType::LOCAL, $files_to_delete_local->all());
				$job->handle();
			}

			if (!$files_to_delete_s3->isEmpty()) {
				$job = new FileDeleterJob(StorageDiskType::S3, $files_to_delete_s3->all());
				$job->handle();
			}
		} catch (Throwable $e) {
			$this->msg_section->writeln('<error>error:</error> Some watermarked files could not be deleted: ' . $e->getMessage());
		}

		if (Schema::hasColumn('size_variants', self::SHORT_PATH_WATERMARKED)) {
			Schema::table('size_variants', function (Blueprint $table) {
				$table->dropColumn(self::SHORT_PATH_WATERMARKED);
			});
		}
	}
};

