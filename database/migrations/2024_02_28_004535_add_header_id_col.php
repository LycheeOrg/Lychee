<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	private const RANDOM_ID_LENGTH = 24;

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('albums', function (Blueprint $table) {
			$table->char('header_id', self::RANDOM_ID_LENGTH)->after('cover_id')->nullable()->default(null);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('albums', function (Blueprint $table) {
			$table->dropColumn('header_id');
		});
	}
};
