<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefactorAlbumModel extends Migration
{
	public function up()
	{
		// There are two very unfortunate "bugs" or missing features in SQLite
		// regarding foreign keys:
		//
		//  1. SQLite drops a foreign key constraint silently, if the
		//     referenced table is renamed.
		//     In other words, the foreign key constraint does not track
		//     the renaming and is updated accordingly, but it simply
		//     vanishes
		//  2. One cannot create a new foreign constraint on an existing
		//     table.
		//     One can only create foreign constraints on a table while the
		//     table itself is created. :-(
		//     This means
		//
		//         Schema::table('my_table', function (Blueprint $table) {
		//           $table->foreign('local_column')->references('foreign_column')->on('foreign_table');
		//         }
		//
		//     does not work, but
		//
		//         Schema::create('my_table', function (Blueprint $table) {
		//           $table->foreign('local_column')->references('foreign_column')->on('foreign_table');
		//         }
		//
		//     works.
		//
		// I also noticed that some foreign constrains that should actually
		// exists are already missing for SQLite.
		// I guess that former migrations have already run into that trap
		// without noticing, because Laravel does not throw an error, if
		// a foreign constraint cannot be created.
		// I checked with my PostgreSQL installation and my SQLite
		// installation and found missing constraints.
		// However, I did not check the actual code of past migrations.
		//
		// As we alter the table `albums` the foreign constraint from
		// `photos` to `albums` via the column `album_id` vanishes.
		// Hence, we must re-create the table `photos`.
		// This has a cascading effect on `size_variants` and in turn on
		// `sym_links`.
		// In other words, we have to re-create the whole database more or
		// less.
		// (At least if we wont to keep foreign constraints in SQLite.)
		// Yikes! :-(

		// Step 1
		// Rename all table to a temporary name so that we get them out
		// of our way.
		// In case of SQLite, this already destroys foreign constraints.
		Schema::rename('albums', 'albums_tmp');
		Schema::rename('user_album', 'user_album_tmp');
		Schema::rename('photos', 'photos_tmp');
		Schema::table('size_variants', function (Blueprint $table) {
			$table->dropUnique('size_variants_photo_id_size_variant_unique');
		});
		Schema::rename('size_variants', 'size_variants_tmp');
		Schema::drop('sym_links');

		// Step 2
		// Recreate tables in correct order so that foreign keys can
		// immediately be created.

		Schema::create('base_albums', function (Blueprint $table) {
			// Column definitions
			$table->bigIncrements('id')->nullable(false);
			$table->dateTime('created_at')->nullable(false);
			$table->dateTime('updated_at')->nullable(false);
			$table->string('title', 100)->nullable(false);
			$table->text('description')->nullable()->default(null);
			$table->unsignedBigInteger('owner_id')->nullable(false)->default(0);
			$table->boolean('public')->nullable(false)->default(false);
			$table->boolean('full_photo')->nullable(false)->default(true);
			$table->boolean('requires_link')->nullable(false)->default(false);
			$table->boolean('downloadable')->nullable(false)->default(false);
			$table->boolean('share_button_visible')->nullable(false)->default(false);
			$table->boolean('nsfw')->nullable(false)->default(false);
			$table->string('password', 100)->nullable()->default(null);
			$table->string('sorting_col', 30)->nullable()->default(null);
			$table->string('sorting_order', 4)->nullable()->default(null);
			// Indices and constraint definitions
			$table->foreign('owner_id')->references('id')->on('users');
		});

		Schema::create('albums', function (Blueprint $table) {
			// Column definitions
			$table->unsignedBigInteger('id')->nullable(false);
			$table->unsignedBigInteger('parent_id')->nullable()->default(null);
			$table->string('license', 20)->nullable(false)->default('none');
			$table->unsignedBigInteger('cover_id')->nullable()->default(null);
			$table->unsignedBigInteger('_lft')->nullable()->default(null);
			$table->unsignedBigInteger('_rgt')->nullable()->default(null);
			// Indices and constraint definitions
			$table->primary('id');
			$table->foreign('id')->references('id')->on('base_albums');
			$table->foreign('parent_id')->references('id')->on('albums');
			// Sic!
			// Columns `created_at` and `updated_at` left out by intention.
			// The albums belong to their "parent" base album and are tied to the same timestamps
		});

		Schema::create('tag_albums', function (Blueprint $table) {
			// Column definitions
			$table->unsignedBigInteger('id')->nullable(false);
			$table->text('show_tags')->nullable();
			// Indices and constraint definitions
			$table->primary('id');
			$table->foreign('id')->references('id')->on('base_albums');
			// Sic!
			// Columns `created_at` and `updated_at` left out by intention.
			// The tag albums belong to their "parent" base album and are tied to the same timestamps
		});

		Schema::create('user_base_album', function (Blueprint $table) {
			// Column definitions
			$table->bigIncrements('id')->nullable(false);
			$table->unsignedInteger('user_id')->nullable(false);
			$table->unsignedBigInteger('album_id')->nullable(false);
			// Indices and constraint definitions
			$table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
			$table->foreign('album_id')->references('id')->on('base_albums')->cascadeOnUpdate()->cascadeOnDelete();
		});

		Schema::create('photos', function (Blueprint $table) {
			// Column definitions
			$table->bigIncrements('id')->nullable(false);
			$table->dateTime('created_at')->nullable(false);
			$table->dateTime('updated_at')->nullable(false);
			$table->unsignedInteger('owner_id')->nullable(false)->default(0);
			$table->unsignedBigInteger('album_id')->nullable()->default(null);
			$table->string('title', 100)->nullable(false);
			$table->text('description')->nullable()->default(null);
			$table->text('tags')->nullable()->default(null);
			$table->string('license', 20)->nullable(false)->default('none');
			$table->boolean('public')->nullable(false)->default(false);
			$table->boolean('star')->nullable(false)->default(false);
			$table->string('iso')->nullable()->default(null);
			$table->string('make')->nullable()->default(null);
			$table->string('model')->nullable()->default(null);
			$table->string('lens')->nullable()->default(null);
			$table->string('aperture')->nullable()->default(null);
			$table->string('shutter')->nullable()->default(null);
			$table->string('focal')->nullable()->default(null);
			$table->decimal('latitude', 10, 8)->nullable()->default(null);
			$table->decimal('longitude', 11, 8)->nullable()->default(null);
			$table->decimal('altitude', 10, 4)->nullable()->default(null);
			$table->decimal('img_direction', 10, 4)->nullable()->default(null);
			$table->string('location')->nullable()->default(null);
			$table->dateTime('taken_at')->nullable(true)->default(null)->comment('relative to UTC');
			$table->string('taken_at_orig_tz', 31)->nullable(true)->default(null)->comment('the timezone at which the photo has originally been taken');
			$table->string('type', 30)->nullable(false);
			$table->unsignedBigInteger('filesize')->nullable(false)->default(0);
			$table->string('checksum', 40)->nullable(false);
			// Indices and constraint definitions
			$table->foreign('owner_id')->references('id')->on('users');
			$table->foreign('album_id')->references('id')->on('albums');
		});

		Schema::create('size_variants', function (Blueprint $table) {
			// Column definitions
			$table->bigIncrements('id')->nullable(false);
			$table->unsignedBigInteger('photo_id')->nullable(false);
			$table->unsignedInteger('size_variant')->nullable(false)->default(0)->comment('0: original, ..., 6: thumb');
			$table->string('short_path')->nullable(false);
			$table->integer('width')->nullable(false);
			$table->integer('height')->nullable(false);
			// Indices and constraint definitions
			$table->unique(['photo_id', 'size_variant']);
			$table->foreign('photo_id')->references('id')->on('photos');
			// Sic!
			// Columns `created_at` and `updated_at` left out by intention.
			// The size variants belong to their "parent" photo model and are tied to the same timestamps
		});

		Schema::create('sym_links', function (Blueprint $table) {
			// Column definitions
			$table->bigIncrements('id')->nullable(false);
			$table->dateTime('created_at')->nullable(false);
			$table->dateTime('updated_at')->nullable(false);
			$table->unsignedBigInteger('size_variant_id')->nullable(false);
			$table->string('short_path')->nullable(false);
			// Indices and constraint definitions
			$table->foreign('size_variant_id')->references('id')->on('size_variants');
		});

		Schema::table('albums', function (Blueprint $table) {
			// We cannot create this foreign constraint on the table albums
			// when it is created, because we have a circular foreign
			// constraint between `photos` and `albums`.
			// Photos point to albums via `album_id` and albums point to
			// photos via `cover_id`.
			// To break up that circular dependencies, we set the constraint on
			// `cover_id` here.
			// This has no effect for SQLite :-(
			$table->foreign('cover_id')->references('id')->on('photos');
		});

		// Step 3
		// Happy copying :(

		DB::beginTransaction();

		$oldAlbums = DB::table('albums_tmp')->lazyById();
		foreach ($oldAlbums as $oldAlbum) {
			DB::table('base_albums')->insert([
				'id' => $oldAlbum->id,
				'created_at' => $oldAlbum->created_at,
				'updated_at' => $oldAlbum->updated_at,
				'title' => $oldAlbum->title,
				'description' => $oldAlbum->description,
				'owner_id' => $oldAlbum->owner_id,
				'public' => $oldAlbum->public,
				'full_photo' => $oldAlbum->full_photo,
				'requires_link' => !($oldAlbum->vieable),
				'downloadable' => $oldAlbum->downloadable,
				'share_button_visible' => $oldAlbum->share_button_visible,
				'nsfw' => $oldAlbum->nsfw,
				'password' => empty($oldAlbum->password) ? null : $oldAlbum->password,
				'sorting_col' => empty($oldAlbum->sorting_col) ? null : $oldAlbum->sorting_col,
				'sorting_order' => empty($oldAlbum->sorting_col) ? null : $oldAlbum->sorting_order,
			]);

			if ($oldAlbum->smart) {
				DB::table('tag_albums')->insert([
					'id' => $oldAlbum->id,
					'show_tags' => $oldAlbum->showtags,
				]);
			} else {
				DB::table('albums')->insert([
					'id' => $oldAlbum->id,
					'parent_id' => $oldAlbum->parent_id,
					'license' => $oldAlbum->license,
					'cover_id' => $oldAlbum->cover_id,
					'_lft' => $oldAlbum->_lft,
					'_rgt' => $oldAlbum->_rgt,
				]);
			}
		}
		// We must remove any foreign link to table `photos`.
		// Otherwise we cannot drop the table `albums_tmp` after the table `photos_tmp`.
		DB::table('albums_tmp')->update(['cover_id' => null]);

		$oldUserAlbumRelations = DB::table('user_album_tmp')->lazyById();
		foreach ($oldUserAlbumRelations as $oldUserAlbumRelation) {
			DB::table('user_base_album')->insert([
				'id' => $oldUserAlbumRelation->id,
				'user_id' => $oldUserAlbumRelation->owner_id,
				'base_album_id' => $oldUserAlbumRelation->album_id,
			]);
		}

		$oldPhotos = DB::table('photos_tmp')->lazyById();
		foreach ($oldPhotos as $oldPhoto) {
			DB::table('photos')->insert([
				'id' => $oldPhoto->id,
				'created_at' => $oldPhoto->created_at,
				'updated_at' => $oldPhoto->updated_at,
				'owner_id' => $oldPhoto->owner_id,
				'album_id' => $oldPhoto->album_id,
				'title' => $oldPhoto->title,
				'description' => empty($oldPhoto->description) ? null : $oldPhoto->description,
				'tags' => empty($oldPhoto->tags) ? null : $oldPhoto->tags,
				'license' => $oldPhoto->license,
				'public' => $oldPhoto->public,
				'star' => $oldPhoto->star,
				'make' => empty($oldPhoto->make) ? null : $oldPhoto->make,
				'model' => empty($oldPhoto->model) ? null : $oldPhoto->model,
				'lens' => empty($oldPhoto->lens) ? null : $oldPhoto->lens,
				'aperture' => empty($oldPhoto->aperture) ? null : $oldPhoto->aperture,
				'shutter' => empty($oldPhoto->shutter) ? null : $oldPhoto->shutter,
				'focal' => empty($oldPhoto->focal) ? null : $oldPhoto->focal,
				'latitude' => $oldPhoto->latitude,
				'longitude' => $oldPhoto->longitude,
				'altitude' => $oldPhoto->altitude,
				'img_direction' => $oldPhoto->img_Direction,
				'location' => empty($oldPhoto->location) ? null : $oldPhoto->location,
				'taken_at' => $oldPhoto->taken_at,
				'taken_at_orig_tz' => $oldPhoto->taken_at_orig_tz,
				'type' => $oldPhoto->type,
				'filesize' => $oldPhoto->filesize,
				'checksum' => $oldPhoto->checksum,
			]);
		}

		$oldSizeVariants = DB::table('size_variants_tmp')->lazyById();
		foreach ($oldSizeVariants as $oldSizeVariant) {
			DB::table('size_variants')->insert([
				'id' => $oldSizeVariant->id,
				'photo_id' => $oldSizeVariant->photo_id,
				'size_variant' => $oldSizeVariant->size_variant,
				'short_path' => $oldSizeVariant->short_path,
				'width' => $oldSizeVariant->width,
				'height' => $oldSizeVariant->height,
			]);
		}

		DB::commit();

		// Step 7
		// Drop the temporary tables.
		// The order is important to avoid error due to unsatisfied foreign
		// constraints.
		Schema::drop('size_variants_tmp');
		Schema::drop('photos_tmp');
		Schema::drop('user_album_tmp');
		Schema::drop('albums_tmp');
	}

	public function down()
	{
	}
}
