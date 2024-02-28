<?php

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

			$table->foreign('header_id')
				->references('id')->on('photos')
				->onUpdate('CASCADE')
				->onDelete('SET NULL');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('albums', function (Blueprint $table) {
			$table->dropForeign('albums_header_id_foreign');

			$table->dropColumn('header_id');
		});
	}
};
