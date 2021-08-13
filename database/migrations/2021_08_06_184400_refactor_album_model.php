<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefactorAlbumModel extends Migration
{
	public function up()
	{
		DB::beginTransaction();

		Schema::table('photos', function (Blueprint $table) {
			$table->dropForeign(['album_id']);
		});
		Schema::table('user_album', function (Blueprint $table) {
			$table->dropForeign(['album_id']);
		});

		Schema::rename('albums', 'base_albums');
		Schema::rename('user_album', 'user_base_album');

		Schema::table('user_base_album', function (Blueprint $table) {
			$table->renameColumn('album_id', 'base_album_id');
		});
		Schema::table('user_base_album', function (Blueprint $table) {
			$table->unsignedBigInteger('base_album_id')->nullable(false)->change();
		});

		Schema::table('base_albums', function (Blueprint $table) {
			$table->renameColumn('viewable', 'requires_link');
			$table->boolean('requires_link')->default(false)->change();
		});

		DB::update('UPDATE base_albums SET requires_link = NOT requires_link');

		Schema::create('albums', function (Blueprint $table) {
			$table->unsignedBigInteger('id')->nullable(false)->primary();
			$table->unsignedBigInteger('parent_id')->nullable()->default(null);
			$table->string('license', 20)->nullable(false)->default('none');
			$table->unsignedBigInteger('cover_id')->nullable()->default(null);
			$table->unsignedBigInteger('_lft')->nullable()->default(null);
			$table->unsignedBigInteger('_rgt')->nullable()->default(null);
		});

		Schema::table('albums', function (Blueprint $table) {
			$table->foreign('id')->references('id')->on('base_albums');
			$table->foreign('parent_id')->references('id')->on('albums');
			$table->foreign('cover_id')->references('id')->on('photos');
		});

		Schema::create('tag_albums', function (Blueprint $table) {
			$table->unsignedBigInteger('id')->nullable(false)->primary();
			$table->text('show_tags')->nullable();
		});

		Schema::table('tag_albums', function (Blueprint $table) {
			$table->foreign('id')->references('id')->on('base_albums');
		});

		Schema::table('photos', function (Blueprint $table) {
			$table->foreign('album_id')->references('id')->on('albums');
		});

		Schema::table('user_base_album', function (Blueprint $table) {
			$table->foreign('base_album_id')->references('id')->on('base_albums')->cascadeOnDelete();
		});

		$baseAlbums = DB::table('base_albums')
			->select([
				'id',
				'smart',
				'showtags',
				'parent_id',
				'license',
				'cover_id',
				'_lft',
				'_rgt',
			])
			->lazyById();

		foreach ($baseAlbums as $baseAlbum) {
			if ($baseAlbum->smart) {
				DB::table('tag_albums')->insert([
					'id' => $baseAlbum->id,
					'show_tags' => $baseAlbum->showtags,
				]);
			} else {
				DB::table('albums')->insert([
					'id' => $baseAlbum->id,
					'parent_id' => $baseAlbum->parent_id,
					'license' => $baseAlbum->license,
					'cover_id' => $baseAlbum->cover_id,
					'_lft' => $baseAlbum->_lft,
					'_rgt' => $baseAlbum->_rgt,
				]);
			}
		}

		Schema::dropColumns('base_albums', [
			'smart',
			'showtags',
			'parent_id',
			'license',
			'cover_id',
			'_lft',
			'_rgt',
		]);

		DB::commit();
	}

	public function down()
	{
		DB::beginTransaction();

		Schema::table('base_albums', function (Blueprint $table) {
			$table->boolean('smart')->default(false);
			$table->text('showtags')->nullable();
			$table->unsignedBigInteger('parent_id')->nullable()->default(null)->index();
			$table->string('license', 20)->nullable(false)->default('none');
			$table->unsignedBigInteger('cover_id')->nullable()->default(null)->index();
			$table->unsignedBigInteger('_lft')->nullable()->default(null);
			$table->unsignedBigInteger('_rgt')->nullable()->default(null);
		});

		$albums = DB::table('albums')
			->select([
				'id',
				'parent_id',
				'license',
				'cover_id',
				'_lft',
				'_rgt',
			])
			->lazyById();

		foreach ($albums as $album) {
			DB::table('base_albums')
				->where('id', '=', $album->id)
				->update([
					'parent_id' => $album->parent_id,
					'license' => $album->license,
					'cover_id' => $album->cover_id,
					'_lft' => $album->_lft,
					'_rgt' => $album->_rgt,
				]);
		}

		$tagAlbums = DB::table('tag_albums')
			->select(['id', 'show_tags'])
			->lazyById();

		foreach ($tagAlbums as $tagAlbum) {
			DB::table('base_albums')
				->where('id', '=', $tagAlbum->id)
				->update([
					'smart' => true,
					'showtags' => $tagAlbum->show_tags,
				]);
		}

		DB::update('UPDATE base_albums SET requires_link = NOT requires_link');

		Schema::table('base_albums', function (Blueprint $table) {
			$table->boolean('requires_link')->default(true)->change();
			$table->renameColumn('requires_link', 'viewable');
		});

		Schema::table('photos', function (Blueprint $table) {
			$table->dropForeign(['album_id']);
		});
		Schema::table('user_base_album', function (Blueprint $table) {
			$table->dropForeign(['base_album_id']);
		});

		Schema::drop('albums');
		Schema::drop('tag_albums');
		Schema::rename('base_albums', 'albums');
		Schema::rename('user_base_album', 'user_album');

		Schema::table('user_album', function (Blueprint $table) {
			$table->renameColumn('base_album_id', 'album_id');
		});
		Schema::table('user_album', function (Blueprint $table) {
			$table->foreign('album_id')->references('id')->on('albums')->cascadeOnDelete();
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->foreign('album_id')->references('id')->on('albums');
		});

		DB::commit();
	}
}
