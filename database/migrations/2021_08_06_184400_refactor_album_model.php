<?php

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for new architecture of albums.
 *
 * There are two very unfortunate "bugs" or missing features in SQLite
 * regarding foreign keys:
 *
 *  1. SQLite drops a foreign key constraint silently, if the
 *     referenced table is renamed.
 *     In other words, the foreign key constraint does not track
 *     the renaming and is updated accordingly, but it simply
 *     vanishes
 *  2. One cannot create a new foreign constraint on an existing
 *     table.
 *     One can only create foreign constraints on a table while the
 *     table itself is created. :-(
 *     This means
 *
 *         Schema::table('my_table', function (Blueprint $table) {
 *           $table->foreign('local_column')->references('foreign_column')->on('foreign_table');
 *         });
 *
 *     does not work, but
 *
 *         Schema::create('my_table', function (Blueprint $table) {
 *           $table->foreign('local_column')->references('foreign_column')->on('foreign_table');
 *         });
 *
 *     works.
 *
 * I also noticed that some foreign constrains that should actually
 * exist are already missing for SQLite.
 * I guess that former migrations have already run into that trap
 * without noticing, because Laravel does not throw an error, if
 * a foreign constraint cannot be created.
 * I checked with my PostgreSQL installation and my SQLite
 * installation and found missing constraints.
 * However, I did not check the actual code of past migrations.
 *
 * As we alter the table `albums` the foreign constraint from
 * `photos` to `albums` via the column `album_id` vanishes.
 * Hence, we must re-create the table `photos`.
 * This has a cascading effect on `size_variants` and in turn on
 * `sym_links`.
 * In other words, we have to re-create the whole database more or
 * less.
 * (At least, if we want to keep foreign constraints in SQLite.)
 * Yikes! :-(
 */
class RefactorAlbumModel extends Migration
{
	private string $driverName;
	private AbstractSchemaManager $schemaManager;

	public function __construct()
	{
		$connection = Schema::connection(null)->getConnection();
		$this->driverName = $connection->getDriverName();
		$this->schemaManager = $connection->getDoctrineSchemaManager();
	}

	public function up()
	{
		// Step 1
		// Create tables in correct order so that foreign keys can
		// be created immediately.
		$this->createBaseAlbumTable();
		$this->renameTables();
		$this->createAlbumTableUp();
		$this->createTagAlbumTable();
		$this->createUserAlbumTable(true);
		$this->createPhotoTableUp();
		$this->createSizeVariantTable();
		$this->createSymLinkTable();
		$this->createRemainingForeignConstraints();

		// Step 2
		// Happy copying :(
		DB::beginTransaction();
		$this->upgradeCopy();
		$this->upgradeConfig();
		DB::commit();

		// Step 3
		Schema::drop('user_album');
		$this->dropTemporarilyRenamedTables();
	}

	public function down()
	{
		// Step 1
		// Create tables in correct order so that foreign keys can
		// be created immediately.
		$this->renameTables();
		$this->createAlbumTableDown();
		$this->createUserAlbumTable(false);
		$this->createPhotoTableDown();
		$this->createSizeVariantTable();
		$this->createSymLinkTable();
		$this->createRemainingForeignConstraints();

		// Step 2
		// Happy copying :(
		DB::beginTransaction();
		$this->downgradeCopy();
		$this->downgradeConfig();
		DB::commit();

		// Step 3
		Schema::drop('user_base_album');
		$this->dropTemporarilyRenamedTables();
		Schema::drop('tag_albums');
		Schema::drop('base_albums');
	}

	/**
	 * Renames some tables to a temporary name so that we get them out of
	 * out way.
	 *
	 * In case of SQLite, this already destroys foreign constraints, but
	 * it does not destroy any other indexes.
	 * Again, we are facing funny differences how the schema abstraction of
	 * Laravel handles SQLite on the on hand side and MySQL/PostgreSQL on the
	 * other hand side.
	 * Hence, we remove all indexes in advance before we rename the table,
	 * so that we can re-create them later without failing.
	 */
	private function renameTables(): void
	{
		Schema::table('albums', function (Blueprint $table) {
			// We must remove any foreign link from `albums` to `photos` to
			// break up circular dependencies.
			$this->dropForeignIfExist($table, 'albums_cover_id_foreign');
			$this->dropIndexIfExist($table, 'albums__lft__rgt_index');
		});
		Schema::rename('albums', 'albums_tmp');
		Schema::table('photos', function (Blueprint $table) {
			$this->dropIndexIfExist($table, 'photos_created_at_index');
			$this->dropIndexIfExist($table, 'photos_updated_at_index');
			$this->dropIndexIfExist($table, 'photos_taken_at_index');
			$this->dropIndexIfExist($table, 'photos_checksum_index');
			$this->dropIndexIfExist($table, 'photos_live_photo_content_id_index');
			$this->dropIndexIfExist($table, 'photos_live_photo_checksum_index');
			$this->dropIndexIfExist($table, 'photos_is_public_index');
			$this->dropIndexIfExist($table, 'photos_is_starred_index');
		});
		Schema::rename('photos', 'photos_tmp');
		Schema::table('size_variants', function (Blueprint $table) {
			$this->dropUniqueIfExist($table, 'size_variants_photo_id_size_variant_unique');
		});
		Schema::rename('size_variants', 'size_variants_tmp');
		Schema::drop('sym_links');
	}

	/**
	 * Drops temporary tables which have been created by
	 * {@link RefactorAlbumModel::renameTables()}.
	 *
	 * The order is important to avoid error due to unsatisfied foreign
	 * constraints.
	 */
	private function dropTemporarilyRenamedTables(): void
	{
		// We must remove any foreign link from `albums` to `photos` to
		// break up circular dependencies.
		DB::table('albums_tmp')->update(['cover_id' => null]);
		Schema::drop('size_variants_tmp');
		Schema::drop('photos_tmp');
		Schema::drop('albums_tmp');
	}

	/**
	 * Creates the table `base_albums`.
	 *
	 * The table `base_albums` contains all columns of the old table
	 * `albums` which are common to normal albums and tag albums.
	 */
	private function createBaseAlbumTable(): void
	{
		Schema::create('base_albums', function (Blueprint $table) {
			// Column definitions
			$table->bigIncrements('id')->nullable(false);
			$table->dateTime('created_at')->nullable(false);
			$table->dateTime('updated_at')->nullable(false);
			$table->string('title', 100)->nullable(false);
			$table->text('description')->nullable()->default(null);
			$table->unsignedBigInteger('owner_id')->nullable(false)->default(0);
			$table->boolean('is_public')->nullable(false)->default(false);
			$table->boolean('grants_full_photo')->nullable(false)->default(true);
			$table->boolean('requires_link')->nullable(false)->default(false);
			$table->boolean('is_downloadable')->nullable(false)->default(false);
			$table->boolean('is_share_button_visible')->nullable(false)->default(false);
			$table->boolean('is_nsfw')->nullable(false)->default(false);
			$table->string('password', 100)->nullable()->default(null);
			$table->string('sorting_col', 30)->nullable()->default(null);
			$table->string('sorting_order', 4)->nullable()->default(null);
			// Indices and constraint definitions
			$table->foreign('owner_id')->references('id')->on('users');
			$table->index('is_public');
		});
	}

	/**
	 * Creates the table `albums` acc. to the new architecture.
	 *
	 * The new table `albums` only contains the columns which are specific
	 * to real albums and are irrelevant for tag albums.
	 */
	private function createAlbumTableUp(): void
	{
		Schema::create('albums', function (Blueprint $table) {
			// Column definitions
			$table->unsignedBigInteger('id')->nullable(false);
			$table->unsignedBigInteger('parent_id')->nullable()->default(null);
			$table->string('license', 20)->nullable(false)->default('none');
			$table->unsignedBigInteger('cover_id')->nullable()->default(null);
			$table->unsignedBigInteger('_lft')->nullable(false)->default(0);
			$table->unsignedBigInteger('_rgt')->nullable(false)->default(0);
			// Indices and constraint definitions
			$table->primary('id');
			$table->index(['_lft', '_rgt']);
			$table->foreign('id')->references('id')->on('base_albums');
			$table->foreign('parent_id')->references('id')->on('albums');
			// Sic!
			// Columns `created_at` and `updated_at` left out by intention.
			// The albums belong to their "parent" base album and are tied to the same timestamps
		});
	}

	/**
	 * Creates the table `tag_albums`.
	 *
	 * The table `tag_albums` only contains the columns which are specific
	 * to tag albums and are irrelevant for real albums.
	 */
	private function createTagAlbumTable(): void
	{
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
	}

	/**
	 * Creates the table `albums` acc. to the old architecture.
	 *
	 * The old table `albums` only contains the union of all columns of
	 * `base_albums`, (the new table) `albums` and `tag_albums`.
	 * Also see
	 * {@link RefactorAlbumModel::createBaseAlbumTable()},
	 * {@link RefactorAlbumModel::createAlbumTableUp()} and
	 * {@link RefactorAlbumModel::createTagAlbumTable()}.
	 */
	private function createAlbumTableDown(): void
	{
		Schema::create('albums', function (Blueprint $table) {
			// Column definitions
			$table->bigIncrements('id')->nullable(false);
			$table->unsignedBigInteger('parent_id')->nullable()->default(null);
			$table->dateTime('created_at')->nullable(false);
			$table->dateTime('updated_at')->nullable(false);
			$table->string('title', 100)->nullable(false);
			$table->text('description')->nullable()->default(null);
			$table->string('license', 20)->nullable(false)->default('none');
			$table->unsignedBigInteger('owner_id')->nullable(false)->default(0);
			$table->boolean('smart')->nullable(false)->default(false);
			$table->text('showtags')->nullable();
			$table->boolean('public')->nullable(false)->default(false);
			$table->boolean('full_photo')->nullable(false)->default(true);
			$table->boolean('viewable')->nullable(false)->default(false);
			$table->boolean('downloadable')->nullable(false)->default(false);
			$table->boolean('share_button_visible')->nullable(false)->default(false);
			$table->boolean('nsfw')->nullable(false)->default(false);
			$table->string('password', 100)->nullable()->default(null);
			$table->unsignedBigInteger('cover_id')->nullable()->default(null);
			$table->string('sorting_col', 30)->nullable()->default(null);
			$table->string('sorting_order', 4)->nullable()->default(null);
			$table->unsignedBigInteger('_lft')->nullable()->default(null);
			$table->unsignedBigInteger('_rgt')->nullable()->default(null);
			// Indices and constraint definitions
			$table->foreign('parent_id')->references('id')->on('albums');
			$table->foreign('owner_id')->references('id')->on('users');
			$table->index(['_lft', '_rgt']);
		});
	}

	/**
	 * Either creates the table `user_base_album` or `user_album`.
	 *
	 * The created table is the pivot table for the (m:n)-relationship between
	 * an owner (user) and an album.
	 * Wrt. the new architecture, the relation links to the table
	 * `base_albums`, wrt. to the old architecture the relation links to the
	 * table `albums`.
	 *
	 * @param bool $isUp True on upgrade path, false on downgrade path
	 */
	private function createUserAlbumTable(bool $isUp): void
	{
		$name = $isUp ? 'base_album' : 'album';

		Schema::create('user_' . $name, function (Blueprint $table) use ($name) {
			// Column definitions
			$table->bigIncrements('id')->nullable(false);
			$table->integer('user_id')->unsigned()->nullable(false);
			$table->unsignedBigInteger($name . '_id')->nullable(false);
			// Indices and constraint definitions
			$table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
			$table->foreign($name . '_id')->references('id')->on($name . 's')->cascadeOnUpdate()->cascadeOnDelete();
		});
	}

	/**
	 * Creates the table `photos` acc. to the new architecture.
	 */
	private function createPhotoTableUp(): void
	{
		Schema::create('photos', function (Blueprint $table) {
			// Column definitions
			$table->bigIncrements('id')->nullable(false);
			$table->dateTime('created_at')->nullable(false);
			$table->dateTime('updated_at')->nullable(false);
			$table->integer('owner_id')->unsinged()->nullable(false)->default(0);
			$table->unsignedBigInteger('album_id')->nullable()->default(null);
			$table->string('title', 100)->nullable(false);
			$table->text('description')->nullable()->default(null);
			$table->text('tags')->nullable()->default(null);
			$table->string('license', 20)->nullable(false)->default('none');
			$table->boolean('is_public')->nullable(false)->default(false);
			$table->boolean('is_starred')->nullable(false)->default(false);
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
			$table->string('live_photo_short_path')->nullable()->default(null);
			$table->string('live_photo_content_id')->nullable()->default(null);
			$table->string('live_photo_checksum', 40)->nullable()->default(null);
			// Indices and constraint definitions
			$table->foreign('owner_id')->references('id')->on('users');
			$table->foreign('album_id')->references('id')->on('albums');
			$table->index('created_at');
			$table->index('updated_at');
			$table->index('taken_at');
			$table->index('checksum');
			$table->index('live_photo_content_id');
			$table->index('live_photo_checksum');
			$table->index('is_public');
			$table->index('is_starred');
		});
	}

	/**
	 * Creates the table `photos` acc. to the old architecture.
	 */
	private function createPhotoTableDown(): void
	{
		Schema::create('photos', function (Blueprint $table) {
			// Column definitions
			$table->bigIncrements('id')->nullable(false);
			$table->dateTime('created_at')->nullable(false);
			$table->dateTime('updated_at')->nullable(false);
			$table->integer('owner_id')->unsigned()->nullable(false)->default(0);
			$table->unsignedBigInteger('album_id')->nullable()->default(null);
			$table->string('title', 100)->nullable(false);
			$table->text('description')->default('');
			$table->text('tags')->nullable(false)->default('');
			$table->string('license', 20)->nullable(false)->default('none');
			$table->boolean('public')->nullable(false)->default(false);
			$table->boolean('star')->nullable(false)->default(false);
			$table->string('iso')->nullable(false)->default('');
			$table->string('make')->nullable(false)->default('');
			$table->string('model')->nullable(false)->default('');
			$table->string('lens')->nullable(false)->default('');
			$table->string('aperture')->nullable(false)->default('');
			$table->string('shutter')->nullable(false)->default('');
			$table->string('focal')->nullable(false)->default('');
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
			$table->string('live_photo_short_path')->nullable()->default(null);
			$table->string('live_photo_content_id')->nullable()->default(null);
			$table->string('live_photo_checksum', 40)->nullable()->default(null);
			// Indices and constraint definitions
			$table->foreign('owner_id')->references('id')->on('users');
			$table->foreign('album_id')->references('id')->on('albums');
			$table->index('created_at');
			$table->index('updated_at');
			$table->index('taken_at');
			$table->index('checksum');
			$table->index('live_photo_content_id');
			$table->index('live_photo_checksum');
		});
	}

	private function createSizeVariantTable(): void
	{
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
	}

	private function createSymLinkTable(): void
	{
		Schema::create('sym_links', function (Blueprint $table) {
			// Column definitions
			$table->bigIncrements('id')->nullable(false);
			$table->dateTime('created_at')->nullable(false);
			$table->dateTime('updated_at')->nullable(false);
			$table->unsignedBigInteger('size_variant_id')->nullable(false);
			$table->string('short_path')->nullable(false);
			// Indices and constraint definitions
			$table->index('created_at');
			$table->index('updated_at');
			$table->foreign('size_variant_id')->references('id')->on('size_variants');
		});
	}

	/**
	 * Creates remaining foreign constraints which could not immediately be
	 * created while the owning table was created due to circular dependencies.
	 *
	 * Note, this method has no effect for a SQLite installation.
	 */
	private function createRemainingForeignConstraints(): void
	{
		Schema::table('albums', function (Blueprint $table) {
			$table->foreign('cover_id')->references('id')->on('photos');
		});
	}

	private function upgradeCopy(): void
	{
		$albums = DB::table('albums_tmp')->lazyById();
		$mapSorting = function (?string $sortingCol): ?string {
			if (empty($sortingCol)) {
				return null;
			} elseif ($sortingCol === 'public') {
				return 'is_public';
			} elseif ($sortingCol === 'star') {
				return 'is_starred';
			} else {
				return $sortingCol;
			}
		};
		foreach ($albums as $album) {
			DB::table('base_albums')->insert([
				'id' => $album->id,
				'created_at' => $album->created_at,
				'updated_at' => $album->updated_at,
				'title' => $album->title,
				'description' => $album->description,
				'owner_id' => $album->owner_id,
				'is_public' => $album->public,
				'grants_full_photo' => $album->full_photo,
				'requires_link' => !($album->viewable),
				'is_downloadable' => $album->downloadable,
				'is_share_button_visible' => $album->share_button_visible,
				'is_nsfw' => $album->nsfw,
				'password' => empty($album->password) ? null : $album->password,
				'sorting_col' => $mapSorting($album->sorting_col),
				'sorting_order' => empty($album->sorting_col) ? null : $album->sorting_order,
			]);

			if ($album->smart) {
				DB::table('tag_albums')->insert([
					'id' => $album->id,
					'show_tags' => $album->showtags,
				]);
			} else {
				DB::table('albums')->insert([
					'id' => $album->id,
					'parent_id' => $album->parent_id,
					'license' => $album->license,
					'cover_id' => $album->cover_id,
					'_lft' => $album->_lft,
					'_rgt' => $album->_rgt,
				]);
			}
		}

		$userAlbumRelations = DB::table('user_album')->lazyById();
		foreach ($userAlbumRelations as $userAlbumRelation) {
			DB::table('user_base_album')->insert([
				'id' => $userAlbumRelation->id,
				'user_id' => $userAlbumRelation->owner_id,
				'base_album_id' => $userAlbumRelation->album_id,
			]);
		}

		$photos = DB::table('photos_tmp')->lazyById();
		foreach ($photos as $photo) {
			DB::table('photos')->insert([
				'id' => $photo->id,
				'created_at' => $photo->created_at,
				'updated_at' => $photo->updated_at,
				'owner_id' => $photo->owner_id,
				'album_id' => $photo->album_id,
				'title' => $photo->title,
				'description' => empty($photo->description) ? null : $photo->description,
				'tags' => empty($photo->tags) ? null : $photo->tags,
				'license' => $photo->license,
				'is_public' => $photo->public,
				'is_starred' => $photo->star,
				'make' => empty($photo->make) ? null : $photo->make,
				'model' => empty($photo->model) ? null : $photo->model,
				'lens' => empty($photo->lens) ? null : $photo->lens,
				'aperture' => empty($photo->aperture) ? null : $photo->aperture,
				'shutter' => empty($photo->shutter) ? null : $photo->shutter,
				'focal' => empty($photo->focal) ? null : $photo->focal,
				'latitude' => $photo->latitude,
				'longitude' => $photo->longitude,
				'altitude' => $photo->altitude,
				'img_direction' => $photo->img_direction,
				'location' => empty($photo->location) ? null : $photo->location,
				'taken_at' => $photo->taken_at,
				'taken_at_orig_tz' => $photo->taken_at_orig_tz,
				'type' => $photo->type,
				'filesize' => $photo->filesize,
				'checksum' => $photo->checksum,
				'live_photo_short_path' => $photo->live_photo_short_path,
				'live_photo_content_id' => $photo->live_photo_content_id,
				'live_photo_checksum' => $photo->live_photo_checksum,
			]);
		}

		$sizeVariants = DB::table('size_variants_tmp')->lazyById();
		foreach ($sizeVariants as $sizeVariant) {
			DB::table('size_variants')->insert([
				'id' => $sizeVariant->id,
				'photo_id' => $sizeVariant->photo_id,
				'size_variant' => $sizeVariant->size_variant,
				'short_path' => $sizeVariant->short_path,
				'width' => $sizeVariant->width,
				'height' => $sizeVariant->height,
			]);
		}
	}

	private function downgradeCopy(): void
	{
		$baseAlbums = DB::table('base_albums')->lazyById();
		$mapSorting = function (?string $sortingCol): ?string {
			if (empty($sortingCol)) {
				return null;
			} elseif ($sortingCol === 'is_public') {
				return 'public';
			} elseif ($sortingCol === 'is_starred') {
				return 'star';
			} else {
				return $sortingCol;
			}
		};
		foreach ($baseAlbums as $oldBaseAlbum) {
			DB::table('albums')->insert([
				'id' => $oldBaseAlbum->id,
				'created_at' => $oldBaseAlbum->created_at,
				'updated_at' => $oldBaseAlbum->updated_at,
				'title' => $oldBaseAlbum->title,
				'description' => $oldBaseAlbum->description,
				'owner_id' => $oldBaseAlbum->owner_id,
				'public' => $oldBaseAlbum->is_public,
				'full_photo' => $oldBaseAlbum->grants_full_photo,
				'viewable' => !($oldBaseAlbum->requires_link),
				'downloadable' => $oldBaseAlbum->is_downloadable,
				'share_button_visible' => $oldBaseAlbum->is_share_button_visible,
				'nsfw' => $oldBaseAlbum->is_nsfw,
				'password' => empty($oldBaseAlbum->password) ? null : $oldBaseAlbum->password,
				'sorting_col' => $mapSorting($oldBaseAlbum->sorting_col),
				'sorting_order' => empty($oldBaseAlbum->sorting_col) ? null : $oldBaseAlbum->sorting_order,
			]);
		}

		$oldAlbums = DB::table('albums_tmp')->lazyById();
		foreach ($oldAlbums as $oldAlbum) {
			DB::table('albums')
				->where('id', '=', $oldAlbum->id)
				->update([
					'smart' => false,
					'parent_id' => $oldAlbum->parent_id,
					'license' => $oldAlbum->license,
					'cover_id' => $oldAlbum->cover_id,
					'_lft' => $oldAlbum->_lft,
					'_rgt' => $oldAlbum->_rgt,
				]);
		}

		$tagAlbums = DB::table('tag_albums')->lazyById();
		foreach ($tagAlbums as $tagAlbum) {
			DB::table('albums')
				->where('id', '=', $tagAlbum->id)
				->update([
					'smart' => true,
					'showtags' => $tagAlbum->show_tags,
				]);
		}

		$userBaseAlbumRelations = DB::table('user_base_album')->lazyById();
		foreach ($userBaseAlbumRelations as $userBaseAlbumRelation) {
			DB::table('user_album')->insert([
				'id' => $userBaseAlbumRelation->id,
				'user_id' => $userBaseAlbumRelation->owner_id,
				'album_id' => $userBaseAlbumRelation->base_album_id,
			]);
		}

		$photos = DB::table('photos_tmp')->lazyById();
		foreach ($photos as $photo) {
			DB::table('photos')->insert([
				'id' => $photo->id,
				'created_at' => $photo->created_at,
				'updated_at' => $photo->updated_at,
				'owner_id' => $photo->owner_id,
				'album_id' => $photo->album_id,
				'title' => $photo->title,
				'description' => empty($photo->description) ? '' : $photo->description,
				'tags' => empty($photo->tags) ? '' : $photo->tags,
				'license' => $photo->license,
				'public' => $photo->is_public,
				'star' => $photo->is_starred,
				'make' => empty($photo->make) ? '' : $photo->make,
				'model' => empty($photo->model) ? '' : $photo->model,
				'lens' => empty($photo->lens) ? '' : $photo->lens,
				'aperture' => empty($photo->aperture) ? '' : $photo->aperture,
				'shutter' => empty($photo->shutter) ? '' : $photo->shutter,
				'focal' => empty($photo->focal) ? '' : $photo->focal,
				'latitude' => $photo->latitude,
				'longitude' => $photo->longitude,
				'altitude' => $photo->altitude,
				'img_direction' => $photo->img_direction,
				'location' => empty($photo->location) ? null : $photo->location,
				'taken_at' => $photo->taken_at,
				'taken_at_orig_tz' => $photo->taken_at_orig_tz,
				'type' => $photo->type,
				'filesize' => $photo->filesize,
				'checksum' => $photo->checksum,
				'live_photo_short_path' => $photo->live_photo_short_path,
				'live_photo_content_id' => $photo->live_photo_content_id,
				'live_photo_checksum' => $photo->live_photo_checksum,
			]);
		}

		$sizeVariants = DB::table('size_variants_tmp')->lazyById();
		foreach ($sizeVariants as $sizeVariant) {
			DB::table('size_variants')->insert([
				'id' => $sizeVariant->id,
				'photo_id' => $sizeVariant->photo_id,
				'size_variant' => $sizeVariant->size_variant,
				'short_path' => $sizeVariant->short_path,
				'width' => $sizeVariant->width,
				'height' => $sizeVariant->height,
			]);
		}
	}

	/**
	 * Upgrades the configuration of default ordering to the new column names.
	 */
	private function upgradeConfig(): void
	{
		DB::table('configs')
			->where('key', '=', 'sorting_Photos_col')
			->update(['type_range' => 'id|taken_at|title|description|is_public|is_starred|type']);
		DB::table('configs')
			->where('key', '=', 'sorting_Photos_col')
			->where('value', '=', 'public')
			->update(['value' => 'is_public']);
		DB::table('configs')
			->where('key', '=', 'sorting_Photos_col')
			->where('value', '=', 'star')
			->update(['value' => 'is_starred']);
		DB::table('configs')
			->where('key', '=', 'sorting_Albums_col')
			->update(['type_range' => 'id|title|description|is_public|max_taken_at|min_taken_at|created_at']);
		DB::table('configs')
			->where('key', '=', 'sorting_Albums_col')
			->where('value', '=', 'public')
			->update(['value' => 'is_public']);
	}

	/**
	 * Downgrades the configuration of default ordering to the new column names.
	 */
	private function downgradeConfig(): void
	{
		DB::table('configs')
			->where('key', '=', 'sorting_Photos_col')
			->update(['type_range' => 'id|taken_at|title|description|public|star|type']);
		DB::table('configs')
			->where('key', '=', 'sorting_Photos_col')
			->where('value', '=', 'is_public')
			->update(['value' => 'public']);
		DB::table('configs')
			->where('key', '=', 'sorting_Photos_col')
			->where('value', '=', 'is_starred')
			->update(['value' => 'star']);
		DB::table('configs')
			->where('key', '=', 'sorting_Albums_col')
			->update(['type_range' => 'id|title|description|public|max_taken_at|min_taken_at|created_at']);
		DB::table('configs')
			->where('key', '=', 'sorting_Albums_col')
			->where('value', '=', 'is_public')
			->update(['value' => 'public']);
	}

	/**
	 * A helper function that allows to drop an index if exists.
	 *
	 * @param Blueprint $table
	 * @param string    $indexName
	 */
	private function dropIndexIfExist(Blueprint $table, string $indexName)
	{
		$doctrineTable = $this->schemaManager->listTableDetails($table->getTable());
		if ($doctrineTable->hasIndex($indexName)) {
			$table->dropIndex($indexName);
		}
	}

	/**
	 * A helper function that allows to drop an index if exists.
	 *
	 * @param Blueprint $table
	 * @param string    $indexName
	 */
	private function dropUniqueIfExist(Blueprint $table, string $indexName)
	{
		$doctrineTable = $this->schemaManager->listTableDetails($table->getTable());
		if ($doctrineTable->hasIndex($indexName)) {
			$table->dropUnique($indexName);
		}
	}

	/**
	 * A helper function that allows to drop an index if exists.
	 *
	 * @param Blueprint $table
	 * @param string    $indexName
	 */
	private function dropForeignIfExist(Blueprint $table, string $indexName)
	{
		if ($this->driverName === 'sqlite') {
			return;
		}
		$doctrineTable = $this->schemaManager->listTableDetails($table->getTable());
		if ($doctrineTable->hasForeignKey($indexName)) {
			$table->dropForeign($indexName);
		}
	}
}
