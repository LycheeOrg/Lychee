<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	public function up(): void
	{
		Schema::table('renamer_rules', function (Blueprint $table) {
			$table->boolean('is_photo_rule')->nullable(false)->default(true)->after('is_enabled');
			$table->boolean('is_album_rule')->nullable(false)->default(true)->after('is_photo_rule');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('base_albums', function (Blueprint $table) {
			$table->dropColumn('is_photo_rule');
			$table->dropColumn('is_album_rule');
		});
	}
};
