<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameAttributesGrants extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::connection(null)->getConnection()->getDriverName() === 'sqlite') {
			Schema::disableForeignKeyConstraints();
		}
		Schema::table('base_albums', function (Blueprint $table) {
			$table->renameColumn('requires_link', 'is_link_required');
		});
		Schema::table('base_albums', function (Blueprint $table) {
			$table->renameColumn('is_downloadable', 'grants_download');
		});
		Schema::table('base_albums', function (Blueprint $table) {
			$table->renameColumn('grants_full_photo', 'grants_full_photo_access');
		});
		if (Schema::connection(null)->getConnection()->getDriverName() === 'sqlite') {
			Schema::enableForeignKeyConstraints();
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (Schema::connection(null)->getConnection()->getDriverName() === 'sqlite') {
			Schema::disableForeignKeyConstraints();
		}
		Schema::table('base_albums', function (Blueprint $table) {
			$table->renameColumn('is_link_required', 'requires_link');
		});
		Schema::table('base_albums', function (Blueprint $table) {
			$table->renameColumn('grants_download', 'is_downloadable');
		});
		Schema::table('base_albums', function (Blueprint $table) {
			$table->renameColumn('grants_full_photo_access', 'grants_full_photo');
		});
		if (Schema::connection(null)->getConnection()->getDriverName() === 'sqlite') {
			Schema::enableForeignKeyConstraints();
		}
	}
}
