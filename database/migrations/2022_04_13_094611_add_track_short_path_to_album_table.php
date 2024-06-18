<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('albums', function (Blueprint $table) {
			$table->string('track_short_path')->after('cover_id')->nullable()->default(null);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('albums', function (Blueprint $table) {
			$table->dropColumn('track_short_path');
		});
	}
};
