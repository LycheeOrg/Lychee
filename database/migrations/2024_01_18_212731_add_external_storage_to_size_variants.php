<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ! remove me in final PR
return new class() extends Migration {
	public function up(): void
	{
		Schema::table('size_variants', function (Blueprint $table) {
			$table->string('external_storage')->nullable();
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->string('external_storage')->after('is_starred')->nullable();
		});
	}

	public function down(): void
	{
		if (Schema::hasColumn('size_variants', 'external_storage')) {
			Schema::table('size_variants', function (Blueprint $table) {
				$table->dropColumn('external_storage');
			});
		}
		if (Schema::hasColumn('photos', 'external_storage')) {
			Schema::table('photos', function (Blueprint $table) {
				$table->dropColumn('external_storage');
			});
		}
	}
};
