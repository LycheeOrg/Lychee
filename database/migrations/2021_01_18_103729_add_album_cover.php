<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	private const ALBUMS = 'albums';
	private const COVER = 'cover_id';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table(self::ALBUMS, function (Blueprint $table) {
			$table->bigInteger(self::COVER)->unsigned()->nullable()->default(null);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table(self::ALBUMS, function (Blueprint $table) {
			$table->dropColumn(self::COVER);
		});
	}
};
