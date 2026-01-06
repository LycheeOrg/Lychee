<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('photos', function (Blueprint $table) {
			// Drop the existing index
			if (Schema::hasIndex('photos', 'photos_album_id_is_starred_title_index')) {
				$table->dropIndex('photos_album_id_is_starred_title_index');
			}

			// Change to text
			$table->text('title')->nullable()->change();

			// Recreate the index with a key length for the TEXT column
			$table->index(['old_album_id', 'is_starred', DB::raw('title(100)')], 'photos_album_id_is_starred_title_index');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('photos', function (Blueprint $table) {
			// Drop the existing index
			if (Schema::hasIndex('photos', 'photos_album_id_is_starred_title_index')) {
				$table->dropIndex('photos_album_id_is_starred_title_index');
			}

			// Change to varchar
			$table->string('title', 100)->nullable()->change();

			$table->index(['odl_album_id', 'is_starred', 'title'], 'photos_album_id_is_starred_title_index');
		});
	}
};
