<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameUrlFields extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('photos', function (Blueprint $table) {
			$table->renameColumn('url', 'filename');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->renameColumn('thumbUrl', 'thumb_filename');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->renameColumn('livePhotoUrl', 'live_photo_filename');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->renameColumn('livePhotoContentID', 'live_photo_content_id');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->renameColumn('livePhotoChecksum', 'live_photo_checksum');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->integer('width')->nullable(false)->change();
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->integer('height')->nullable(false)->change();
		});

		// Table size_variants
		Schema::create('size_variants', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->dateTime('created_at')->nullable(false);
			$table->dateTime('updated_at')->nullable(false);
			$table->foreignId('photo_id')->nullable(false)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
			$table->unsignedInteger('size_variant')->nullable(false)->default(0)->comment('0: original, ..., 7: thumb');
			$table->unique(['photo_id', 'size_variant']);
			$table->string('short_path')->nullable(false);
			$table->integer('width')->nullable(false)->change();
			$table->integer('height')->nullable(false)->change();
		});

		// Table sym_links
		Schema::dropIfExists('sym_links');
		Schema::create('sym_links', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->dateTime('created_at')->nullable(false);
			$table->dateTime('updated_at')->nullable(false);
			$table->foreignId('size_variant_id')->nullable(false)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
			$table->unique('size_variant_id');
			$table->string('short_path')->nullable(false);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('photos', function (Blueprint $table) {
			$table->renameColumn('filename', 'url');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->renameColumn('thumb_filename', 'thumbUrl');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->renameColumn('live_photo_filename', 'livePhotoUrl');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->renameColumn('live_photo_content_id', 'livePhotoContentID');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->renameColumn('live_photo_checksum', 'livePhotoChecksum');
		});

		Schema::dropIfExists('size_variants');

		Schema::dropIfExists('sym_links');
		Schema::create('sym_links', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->bigInteger('photo_id')->nullable();
			$table->string('url')->default('');
			$table->string('medium')->default('');
			$table->string('medium2x')->default('');
			$table->string('small')->default('');
			$table->string('small2x')->default('');
			$table->string('thumbUrl')->default('');
			$table->string('thumb2x')->default('');
			$table->timestamps();
		});
	}
}
