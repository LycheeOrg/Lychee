<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('users', function (Blueprint $table) {
			$table->bigInteger('quota_kb')->after('remember_token')->nullable(true)->default(null);
			$table->text('description')->after('quota_kb')->nullable(true)->default(null);
			$table->text('note')->after('description')->nullable(true)->default(null);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn('quota_kb');
		});

		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn('description');
		});
		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn('note');
		});
	}
};
