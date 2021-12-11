<?php

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Kalnoy\Nestedset\Node;
use Kalnoy\Nestedset\NodeTrait;
use League\Flysystem\FileNotFoundException;

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
class RefactorModels extends Migration
{
	private string $driverName;
	private AbstractSchemaManager $schemaManager;
	public const THUMBNAIL_DIM = 200;
	public const THUMBNAIL2X_DIM = 400;

	public const VARIANT_ORIGINAL = 0;
	public const VARIANT_MEDIUM2X = 1;
	public const VARIANT_MEDIUM = 2;
	public const VARIANT_SMALL2X = 3;
	public const VARIANT_SMALL = 4;
	public const VARIANT_THUMB2X = 5;
	public const VARIANT_THUMB = 6;

	public const RANDOM_ID_LENGTH = 24;

	/**
	 * Maps a size variant (0...6) to the path prefix (directory) where the
	 * file for that size variant is stored.
	 */
	public const VARIANT_2_PATH_PREFIX = [
		'big',
		'medium',
		'medium',
		'small',
		'small',
		'thumb',
		'thumb',
	];

	public const VALID_VIDEO_TYPES = [
		'video/mp4',
		'video/mpeg',
		'image/x-tga', // mpg; will be corrected by the metadata extractor
		'video/ogg',
		'video/webm',
		'video/quicktime',
		'video/x-ms-asf', // wmv file
		'video/x-ms-wmv', // wmv file
		'video/x-msvideo', // Avi
		'video/x-m4v', // Avi
		'application/octet-stream', // Some mp4 files; will be corrected by the metadata extractor
	];

	/**
	 * Maps a size variant (0...4) to the name of the (old) attribute which
	 * stores the width of that size variant.
	 * Note: No attribute is defined for the size variants 5 and 6 (`thumb2x`
	 * and `thumb`), because their width is not stored as an attribute but
	 * hard-coded.
	 * See {@link RefactorModels::THUMBNAIL2X_DIM} and
	 * {@link RefactorModels::THUMBNAIL_DIM}.
	 */
	public const VARIANT_2_WIDTH_ATTRIBUTE = [
		'width',
		'medium2x_width',
		'medium_width',
		'small2x_width',
		'small_width',
	];

	/**
	 * Maps a size variant (0...4) to the name of the (old) attribute which
	 * stores the height of that size variant.
	 * Note: No attribute is defined for the size variants 5 and 6 (`thumb2x`
	 * and `thumb`), because their width is not stored as an attribute but
	 * hard-coded.
	 * See {@link RefactorModels::THUMBNAIL2X_DIM} and
	 * {@link RefactorModels::THUMBNAIL_DIM}.
	 */
	public const VARIANT_2_HEIGHT_ATTRIBUTE = [
		'height',
		'medium2x_height',
		'medium_height',
		'small2x_height',
		'small_height',
	];

	/**
	 * Translates album IDs.
	 *
	 * During upgrade the array maps legacy, time-based IDs to new, random IDs.
	 * During downgrade the array maps random IDs to legacy, time-based IDs.
	 *
	 * @var array
	 */
	private array $albumIDCache = [];

	/**
	 * Translates photo IDs.
	 *
	 * During upgrade the array maps legacy, time-based IDs to new, random IDs.
	 * During downgrade the array maps random IDs to legacy, time-based IDs.
	 *
	 * @var array
	 */
	private array $photoIDCache = [];

	/**
	 * @throws DBALException
	 */
	public function __construct()
	{
		$connection = Schema::connection(null)->getConnection();
		$this->driverName = $connection->getDriverName();
		$this->schemaManager = $connection->getDoctrineSchemaManager();
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function up()
	{
		Schema::drop('sym_links');

		// Step 1
		// Create tables in correct order so that foreign keys can
		// be created immediately.
		$this->createBaseAlbumTable();
		$this->renameTables();
		$this->createAlbumTableUp();
		$this->createTagAlbumTable();
		$this->createUserBaseAlbumTableUp();
		$this->createPhotoTableUp();
		$this->createSizeVariantTableUp();
		$this->createSymLinkTableUp();
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

	/**
	 * @throws InvalidArgumentException
	 */
	public function down()
	{
		Schema::drop('sym_links');

		// Step 1
		// Create tables in correct order so that foreign keys can
		// be created immediately.
		$this->renameTables();
		$this->createAlbumTableDown();
		$this->createUserAlbumTableDown();
		$this->createPhotoTableDown();
		$this->createSymLinkTableDown();

		// Step 2
		// Happy copying :(
		DB::beginTransaction();
		$this->downgradeCopy();
		$this->downgradeConfig();
		DB::commit();

		// Step 3
		Schema::drop('user_base_album');
		Schema::drop('size_variants');
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
			$this->dropForeignIfExists($table, 'albums_cover_id_foreign');
			$this->dropForeignIfExists($table, 'albums_parent_id_foreign');
			$this->dropIndexIfExists($table, 'albums__lft__rgt_index');
		});
		Schema::rename('albums', 'albums_tmp');
		Schema::table('photos', function (Blueprint $table) {
			$this->dropForeignIfExists($table, 'photos_album_id_foreign');
			$this->dropForeignIfExists($table, 'photos_owner_id_foreign');
			$this->dropIndexIfExists($table, 'photos_created_at_index');
			$this->dropIndexIfExists($table, 'photos_updated_at_index');
			$this->dropIndexIfExists($table, 'photos_taken_at_index');
			$this->dropIndexIfExists($table, 'photos_checksum_index');
			$this->dropIndexIfExists($table, 'photos_live_photo_content_id_index');
			$this->dropIndexIfExists($table, 'photos_livephotocontentid_index');
			$this->dropIndexIfExists($table, 'photos_live_photo_checksum_index');
			$this->dropIndexIfExists($table, 'photos_livephotochecksum_index');
			$this->dropIndexIfExists($table, 'photos_is_public_index');
			$this->dropIndexIfExists($table, 'photos_is_starred_index');
		});
		Schema::rename('photos', 'photos_tmp');
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
			$table->char('id', self::RANDOM_ID_LENGTH)->nullable(false);
			$table->unsignedBigInteger('legacy_id')->nullable(false);
			$table->dateTime('created_at')->nullable(false);
			$table->dateTime('updated_at')->nullable(false);
			$table->string('title', 100)->nullable(false);
			$table->text('description')->nullable();
			$table->unsignedInteger('owner_id')->nullable(false)->default(0);
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
			$table->primary('id');
			$table->unique('legacy_id');
			$table->foreign('owner_id')->references('id')->on('users');
			// These indices are required for efficient filtering for accessible and/or visible albums
			$table->index(['requires_link', 'is_public']); // for albums which don't require a direct link and are public
			$table->index(['owner_id']); // for albums which are own by the currently authenticated user
			$table->index(['is_public', 'password']); // for albums which are public and how no password
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
			$table->char('id', self::RANDOM_ID_LENGTH)->nullable(false);
			$table->char('parent_id', self::RANDOM_ID_LENGTH)->nullable()->default(null);
			$table->string('license', 20)->nullable(false)->default('none');
			$table->char('cover_id', self::RANDOM_ID_LENGTH)->nullable()->default(null);
			$table->unsignedBigInteger('_lft')->nullable(false)->default(0);
			$table->unsignedBigInteger('_rgt')->nullable(false)->default(0);
			// Indices and constraint definitions
			$table->primary('id');
			$table->index([DB::raw('_lft asc'), DB::raw('_rgt desc')], 'albums__lft__rgt__index');
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
			$table->char('id', self::RANDOM_ID_LENGTH)->nullable(false);
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
			$table->text('description')->nullable();
			$table->string('license', 20)->nullable(false)->default('none');
			$table->unsignedInteger('owner_id')->nullable(false)->default(0);
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
		});
	}

	/**
	 * Creates the table `user_base_album`.
	 *
	 * The created table is the pivot table for the (m:n)-relationship between
	 * an owner (user) and a base album.
	 */
	private function createUserBaseAlbumTableUp(): void
	{
		Schema::create('user_base_album', function (Blueprint $table) {
			// Column definitions
			$table->bigIncrements('id')->nullable(false);
			$table->unsignedInteger('user_id')->nullable(false);
			$table->char('base_album_id', self::RANDOM_ID_LENGTH)->nullable(false);
			// Indices and constraint definitions
			$table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
			$table->foreign('base_album_id')->references('id')->on('base_albums')->cascadeOnUpdate()->cascadeOnDelete();
			// This index is required to efficiently filter those albums
			// which are shared with a particular user
			$table->unique(['base_album_id', 'user_id']);
		});
	}

	/**
	 * Creates the table `user_album`.
	 *
	 * The created table is the pivot table for the (m:n)-relationship between
	 * an owner (user) and an album.
	 */
	private function createUserAlbumTableDown(): void
	{
		Schema::create('user_album', function (Blueprint $table) {
			// Column definitions
			$table->bigIncrements('id')->nullable(false);
			$table->unsignedInteger('user_id')->nullable(false);
			$table->unsignedBigInteger('album_id')->nullable(false);
			// Indices and constraint definitions
			$table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
			$table->foreign('album_id')->references('id')->on('albums')->cascadeOnUpdate()->cascadeOnDelete();
		});
	}

	/**
	 * Creates the table `photos` acc. to the new architecture.
	 */
	private function createPhotoTableUp(): void
	{
		Schema::create('photos', function (Blueprint $table) {
			// Column definitions
			$table->char('id', self::RANDOM_ID_LENGTH)->nullable(false);
			$table->unsignedBigInteger('legacy_id')->nullable(false);
			$table->dateTime('created_at')->nullable(false);
			$table->dateTime('updated_at')->nullable(false);
			$table->unsignedInteger('owner_id')->unsigned()->nullable(false)->default(0);
			$table->char('album_id', self::RANDOM_ID_LENGTH)->nullable()->default(null);
			$table->string('title', 100)->nullable(false);
			$table->text('description')->nullable();
			$table->text('tags')->nullable();
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
			$table->primary('id');
			$table->unique('legacy_id');
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
			// This index is needed to efficiently add the range of take dates
			// to each album.
			$table->index(['album_id', 'taken_at']);
			// These indices are needed to efficiently list all photos of an
			// album acc. to different sorting criteria
			// Upload time, take date, is starred or is public
			$table->index(['album_id', 'created_at']);
			$table->index(['album_id', 'is_starred']);
			$table->index(['album_id', 'is_public']);
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
			$table->unsignedInteger('owner_id')->nullable(false)->default(0);
			$table->unsignedBigInteger('album_id')->nullable()->default(null);
			$table->string('title', 100)->nullable(false);
			$table->text('description')->nullable(true);
			$table->string('tags')->nullable(false)->default('');
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
			$table->decimal('imgDirection', 10, 4)->nullable()->default(null);
			$table->string('location')->nullable()->default(null);
			$table->dateTime('taken_at')->nullable(true)->default(null)->comment('relative to UTC');
			$table->string('taken_at_orig_tz', 31)->nullable(true)->default(null)->comment('the timezone at which the photo has originally been taken');
			$table->string('type', 30)->nullable(false);
			$table->string('url', 100)->default('');
			$table->unsignedBigInteger('filesize')->nullable(false)->default(0);
			$table->string('checksum', 40)->nullable(false);
			for ($i = self::VARIANT_ORIGINAL; $i <= self::VARIANT_SMALL; $i++) {
				$table->integer(self::VARIANT_2_WIDTH_ATTRIBUTE[$i])->unsigned()->nullable()->default(null);
				$table->integer(self::VARIANT_2_HEIGHT_ATTRIBUTE[$i])->unsigned()->nullable()->default(null);
			}
			$table->boolean('thumb2x')->default(false);
			$table->string('thumbUrl', 37)->default('');
			$table->string('livePhotoUrl')->nullable()->default(null);
			$table->string('livePhotoContentID')->nullable()->default(null);
			$table->string('livePhotoChecksum', 40)->nullable()->default(null);
			// Indices and constraint definitions
			$table->foreign('album_id')->references('id')->on('albums');
		});
	}

	private function createSizeVariantTableUp(): void
	{
		Schema::create('size_variants', function (Blueprint $table) {
			// Column definitions
			$table->bigIncrements('id')->nullable(false);
			$table->char('photo_id', self::RANDOM_ID_LENGTH)->nullable(false);
			$table->unsignedInteger('type')->nullable(false)->default(0)->comment('0: original, ..., 6: thumb');
			$table->string('short_path')->nullable(false);
			$table->integer('width')->nullable(false);
			$table->integer('height')->nullable(false);
			// Indices and constraint definitions
			$table->unique(['photo_id', 'type']);
			$table->foreign('photo_id')->references('id')->on('photos');
			// Sic!
			// Columns `created_at` and `updated_at` left out by intention.
			// The size variants belong to their "parent" photo model and are tied to the same timestamps
		});
	}

	private function createSymLinkTableUp(): void
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
			// This index is needed to efficiently find the latest symbolic link
			// for each size variant
			$table->index(['size_variant_id', 'created_at']);
		});
	}

	private function createSymLinkTableDown(): void
	{
		Schema::create('sym_links', function (Blueprint $table) {
			// Column definitions
			$table->bigIncrements('id')->nullable(false);
			$table->dateTime('created_at')->nullable(false);
			$table->dateTime('updated_at')->nullable(false);
			$table->unsignedBigInteger('photo_id')->nullable(false);
			$table->string('url')->default('');
			$table->string('medium')->default('');
			$table->string('medium2x')->default('');
			$table->string('small')->default('');
			$table->string('small2x')->default('');
			$table->string('thumbUrl')->default('');
			$table->string('thumb2x')->default('');
			// Indices and constraint definitions
			$table->foreign('photo_id')->references('id')->on('photos');
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

	/**
	 * @throws InvalidArgumentException
	 */
	private function upgradeCopy(): void
	{
		// Ordering by `_lft` is important, because we must copy parent
		// albums first.
		// Otherwise, foreign key constraint to `parent_id` may fail.
		$albums = DB::table('albums_tmp')->orderBy('_lft')->lazyById();
		$mapSorting = function (?string $sortingCol): ?string {
			if (empty($sortingCol)) {
				return null;
			} elseif ($sortingCol === 'id') {
				return 'created_at';
			} elseif ($sortingCol === 'public') {
				return 'is_public';
			} elseif ($sortingCol === 'star') {
				return 'is_starred';
			} else {
				return $sortingCol;
			}
		};
		foreach ($albums as $album) {
			$newAlbumID = $this->generateKey();
			$this->albumIDCache[strval($album->id)] = $newAlbumID;

			DB::table('base_albums')->insert([
				'id' => $newAlbumID,
				'legacy_id' => $album->id,
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
					'id' => $newAlbumID,
					'show_tags' => $album->showtags,
				]);
			} else {
				// Don't copy `cover_id` yet, because the photos have not been
				// copied yet.
				// Explicit `cover_id` needs to be set belated.
				// Otherwise, the foreign key constraint between `cover_id`
				// and `photos.id` fails.
				DB::table('albums')->insert([
					'id' => $newAlbumID,
					'parent_id' => $album->parent_id ? $this->albumIDCache[strval($album->parent_id)] : null,
					'license' => $album->license,
					'cover_id' => null,
					'_lft' => $album->_lft ?? 0,
					'_rgt' => $album->_rgt ?? 0,
				]);
			}
		}

		RefactorAlbumModel_AlbumModel::query()->fixTree();

		$userAlbumRelations = DB::table('user_album')->lazyById();
		foreach ($userAlbumRelations as $userAlbumRelation) {
			DB::table('user_base_album')->insert([
				'id' => $userAlbumRelation->id,
				'user_id' => $userAlbumRelation->user_id,
				'base_album_id' => $this->albumIDCache[strval($userAlbumRelation->album_id)],
			]);
		}

		$photos = DB::table('photos_tmp')->lazyById();
		foreach ($photos as $photo) {
			$newPhotoID = $this->generateKey();
			$this->photoIDCache[strval($photo->id)] = $newPhotoID;

			DB::table('photos')->insert([
				'id' => $newPhotoID,
				'legacy_id' => $photo->id,
				'created_at' => $photo->created_at,
				'updated_at' => $photo->updated_at,
				'owner_id' => $photo->owner_id,
				'album_id' => $photo->album_id ? $this->albumIDCache[strval($photo->album_id)] : null,
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
				'img_direction' => empty($photo->imgDirection) ? null : $photo->imgDirection,
				'location' => empty($photo->location) ? null : $photo->location,
				'taken_at' => $photo->taken_at,
				'taken_at_orig_tz' => $photo->taken_at_orig_tz,
				'type' => $photo->type,
				'filesize' => $photo->filesize,
				'checksum' => $photo->checksum,
				'live_photo_short_path' => $photo->livePhotoUrl,
				'live_photo_content_id' => $photo->livePhotoContentID,
				'live_photo_checksum' => $photo->livePhotoChecksum,
			]);

			for ($variantType = self::VARIANT_ORIGINAL; $variantType <= self::VARIANT_THUMB; $variantType++) {
				if ($this->hasSizeVariant($photo, $variantType)) {
					DB::table('size_variants')->insert([
						'photo_id' => $newPhotoID,
						'type' => $variantType,
						'short_path' => $this->getShortPathOfPhoto($photo, $variantType),
						'width' => $this->getWidth($photo, $variantType),
						'height' => $this->getHeight($photo, $variantType),
					]);
				}
			}

			// Restore explicit covers of albums
			$coveredAlbums = DB::table('albums_tmp')
				->whereNotNull('cover_id')
				->where('smart', '=', false)
				->lazyById();
			foreach ($coveredAlbums as $coveredAlbum) {
				DB::table('albums')
					->where('id', '=', $this->albumIDCache[strval($coveredAlbum->id)])
					->update(['cover_id' => $this->photoIDCache[strval($coveredAlbum->cover_id)]]);
			}
		}
	}

	/**
	 * @throws InvalidArgumentException
	 */
	private function downgradeCopy(): void
	{
		$baseAlbums = DB::table('base_albums')->lazyById();
		$mapSorting = function (?string $sortingCol): ?string {
			if (empty($sortingCol)) {
				return null;
			} elseif ($sortingCol === 'created_at') {
				return 'id';
			} elseif ($sortingCol === 'is_public') {
				return 'public';
			} elseif ($sortingCol === 'is_starred') {
				return 'star';
			} else {
				return $sortingCol;
			}
		};
		foreach ($baseAlbums as $oldBaseAlbum) {
			$legacyAlbumID = intval($oldBaseAlbum->legacy_id);
			$this->albumIDCache[$oldBaseAlbum->id] = $legacyAlbumID;

			DB::table('albums')->insert([
				'id' => $legacyAlbumID,
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

		// Ordering by `_lft` is important, because we must copy parent
		// albums first.
		// Otherwise, foreign key constraint to `parent_id` may fail.
		// Also, don't copy `cover_id` yet, because the photos have not been
		// copied yet.
		// Explicit `cover_id` needs to be set belated.
		$albums = DB::table('albums_tmp')->orderBy('_lft')->lazyById();
		foreach ($albums as $album) {
			DB::table('albums')
				->where('id', '=', $this->albumIDCache[$album->id])
				->update([
					'smart' => false,
					'parent_id' => $album->parent_id ? $this->albumIDCache[$album->parent_id] : null,
					'license' => $album->license,
					'cover_id' => null,
					'_lft' => $album->_lft,
					'_rgt' => $album->_rgt,
				]);
		}

		$tagAlbums = DB::table('tag_albums')->lazyById();
		foreach ($tagAlbums as $tagAlbum) {
			DB::table('albums')
				->where('id', '=', $this->albumIDCache[$tagAlbum->id])
				->update([
					'smart' => true,
					'showtags' => $tagAlbum->show_tags,
				]);
		}

		RefactorAlbumModel_AlbumModel::query()->fixTree();

		$userBaseAlbumRelations = DB::table('user_base_album')->lazyById();
		foreach ($userBaseAlbumRelations as $userBaseAlbumRelation) {
			DB::table('user_album')->insert([
				'id' => $userBaseAlbumRelation->id,
				'user_id' => $userBaseAlbumRelation->user_id,
				'album_id' => $this->albumIDCache[$userBaseAlbumRelation->base_album_id],
			]);
		}

		$photos = DB::table('photos_tmp')->lazyById();
		foreach ($photos as $photo) {
			$legacyPhotoID = intval($photo->legacy_id);
			$this->photoIDCache[$photo->id] = $legacyPhotoID;
			$photoAttributes = [
				'id' => $legacyPhotoID,
				'created_at' => $photo->created_at,
				'updated_at' => $photo->updated_at,
				'owner_id' => $photo->owner_id,
				'album_id' => $photo->album_id ? $this->albumIDCache[$photo->album_id] : null,
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
				'imgDirection' => $photo->img_direction,
				'location' => empty($photo->location) ? null : $photo->location,
				'taken_at' => $photo->taken_at,
				'taken_at_orig_tz' => $photo->taken_at_orig_tz,
				'type' => $photo->type,
				'filesize' => $photo->filesize,
				'checksum' => $photo->checksum,
				'livePhotoUrl' => $photo->live_photo_short_path,
				'livePhotoContentID' => $photo->live_photo_content_id,
				'livePhotoChecksum' => $photo->live_photo_checksum,
			];

			// Get all size variants for the photo and explicitly extract
			// the size variant "original".
			// If there are no size variants at all or a size variant
			// "original" does not exist, continue.
			// Note, this is actually an error, because there must not
			// be any photo without at least a size variant "original".
			$sizeVariants = DB::table('size_variants')
				->where('photo_id', '=', $photo->id)
				->orderBy('type')
				->get();
			if ($sizeVariants->isEmpty()) {
				continue;
			}
			$originalSizeVariant = $sizeVariants->first();
			if ($originalSizeVariant->type != self::VARIANT_ORIGINAL) {
				continue;
			}

			// We use the original size variant as a baseline to extract the
			// common core of the basename of all size variants.
			// Note: The newly introduced `SizeVariantNamingStrategy`
			// effectively allows that each size variant uses its own file
			// name which may be completely independent of the file names of
			// the other size variants.
			// However, the old code assumes that the file names follow a
			// certain naming pattern which is built around a shared and
			// equal part within the file's basename.
			// Moreover, this common portion must not be longer than 32
			// characters.
			$expectedBasename = substr(
				pathinfo($originalSizeVariant->short_path, PATHINFO_FILENAME),
				0,
				32
			);

			/**
			 * Iterate over all size variants and ensure that they are named
			 * as expected by the old naming scheme.
			 *
			 * @var object $sizeVariant
			 */
			foreach ($sizeVariants as $sizeVariant) {
				$fileExtension = '.' . pathinfo($sizeVariant->short_path, PATHINFO_EXTENSION);
				if (
					$sizeVariant->type == self::VARIANT_THUMB2X ||
					$sizeVariant->type == self::VARIANT_SMALL2X ||
					$sizeVariant->type == self::VARIANT_MEDIUM2X
				) {
					$expectedFilename = $expectedBasename . '@2x' . $fileExtension;
				} else {
					$expectedFilename = $expectedBasename . $fileExtension;
				}
				$expectedPathPrefix = self::VARIANT_2_PATH_PREFIX[$sizeVariant->type] . '/';
				if ($sizeVariant->type == self::VARIANT_ORIGINAL && $this->isRaw($photo)) {
					$expectedPathPrefix = 'raw/';
				}
				$expectedShortPath = $expectedPathPrefix . $expectedFilename;

				// Ensure that the size variant is stored at the location which
				// is expected acc. to the old naming scheme
				if ($sizeVariant->short_path != $expectedShortPath) {
					try {
						Storage::move($sizeVariant->short_path, $expectedShortPath);
					} catch (FileNotFoundException $e) {
						// sic! just ignore
						// This exception is thrown if there are duplicate
						// photos which point to the same physical file.
						// Then the file is renamed when the first occurrence
						// of those duplicates is processed and subsequent,
						// failing attempts to rename the file must be ignored.
					}
				}

				if ($sizeVariant->type == self::VARIANT_THUMB2X) {
					$photoAttributes['thumb2x'] = true;
				} elseif ($sizeVariant->type == self::VARIANT_THUMB) {
					$photoAttributes['thumbUrl'] = $expectedFilename;
				} else {
					if ($sizeVariant->type == self::VARIANT_ORIGINAL) {
						$photoAttributes['url'] = $expectedFilename;
					}
					$photoAttributes[self::VARIANT_2_WIDTH_ATTRIBUTE[$sizeVariant->type]] = $sizeVariant->width;
					$photoAttributes[self::VARIANT_2_HEIGHT_ATTRIBUTE[$sizeVariant->type]] = $sizeVariant->height;
				}
			}

			DB::table('photos')->insert($photoAttributes);
		}

		// Restore explicit covers of albums
		$coveredAlbums = DB::table('albums_tmp')
			->whereNotNull('cover_id')
			->lazyById();
		foreach ($coveredAlbums as $coveredAlbum) {
			DB::table('albums')
				->where('id', '=', $this->albumIDCache[$coveredAlbum->id])
				->update(['cover_id' => $this->photoIDCache[$coveredAlbum->cover_id]]);
		}
	}

	/**
	 * Upgrades the configuration of default ordering to the new column names.
	 *
	 * @throws InvalidArgumentException
	 */
	private function upgradeConfig(): void
	{
		DB::table('configs')
			->where('key', '=', 'sorting_Photos_col')
			->update(['type_range' => 'created_at|taken_at|title|description|is_public|is_starred|type']);
		DB::table('configs')
			->where('key', '=', 'sorting_Photos_col')
			->where('value', '=', 'id')
			->update(['value' => 'created_at']);
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
			->update(['type_range' => 'created_at|title|description|is_public|max_taken_at|min_taken_at']);
		DB::table('configs')
			->where('key', '=', 'sorting_Albums_col')
			->where('value', '=', 'id')
			->update(['value' => 'created_at']);
		DB::table('configs')
			->where('key', '=', 'sorting_Albums_col')
			->where('value', '=', 'public')
			->update(['value' => 'is_public']);
	}

	/**
	 * Downgrades the configuration of default ordering to the new column names.
	 *
	 * @throws InvalidArgumentException
	 */
	private function downgradeConfig(): void
	{
		DB::table('configs')
			->where('key', '=', 'sorting_Photos_col')
			->update(['type_range' => 'id|taken_at|title|description|public|star|type']);
		DB::table('configs')
			->where('key', '=', 'sorting_Photos_col')
			->where('value', '=', 'created_at')
			->update(['value' => 'id']);
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
			->update(['type_range' => 'id|title|description|public|max_taken_at|min_taken_at']);
		DB::table('configs')
			->where('key', '=', 'sorting_Albums_col')
			->where('value', '=', 'created_at')
			->update(['value' => 'id']);
		DB::table('configs')
			->where('key', '=', 'sorting_Albums_col')
			->where('value', '=', 'is_public')
			->update(['value' => 'public']);
	}

	/**
	 * Returns the short path of a picture file for the designated size
	 * variant from an old-style photo wrt. to the old naming scheme.
	 *
	 * @param object $photo an object with attributes of the old photo table
	 *
	 * @return string the short path
	 *
	 * @throws InvalidArgumentException
	 */
	public function getShortPathOfPhoto(object $photo, int $variant): string
	{
		$origFilename = $photo->url;
		$thumbFilename = $photo->thumbUrl;
		$thumbFilename2x = $this->add2xToFilename($thumbFilename);
		$otherFilename = ($this->isVideo($photo) || $this->isRaw($photo)) ? $thumbFilename : $origFilename;
		$otherFilename2x = $this->add2xToFilename($otherFilename);
		switch ($variant) {
			case self::VARIANT_THUMB:
				$filename = $thumbFilename;
				break;
			case self::VARIANT_THUMB2X:
				$filename = $thumbFilename2x;
				break;
			case self::VARIANT_SMALL:
			case self::VARIANT_MEDIUM:
				$filename = $otherFilename;
				break;
			case self::VARIANT_SMALL2X:
			case self::VARIANT_MEDIUM2X:
				$filename = $otherFilename2x;
				break;
			case self::VARIANT_ORIGINAL:
				$filename = $origFilename;
				break;
			default:
				throw new InvalidArgumentException('Invalid size variant: ' . $variant);
		}
		$directory = self::VARIANT_2_PATH_PREFIX[$variant] . '/';
		if ($variant === self::VARIANT_ORIGINAL && $this->isRaw($photo)) {
			$directory = 'raw/';
		}

		return $directory . $filename;
	}

	protected function isVideo(object $photo): bool
	{
		return in_array($photo->type, self::VALID_VIDEO_TYPES, true);
	}

	protected function isRaw(object $photo): bool
	{
		return $photo->type == 'raw';
	}

	/**
	 * Given a filename generates the @2x corresponding filename.
	 * This is used for thumbs, small and medium.
	 */
	protected function add2xToFilename(string $filename): string
	{
		$filename2x = explode('.', $filename);

		return (count($filename2x) === 2) ?
			$filename2x[0] . '@2x.' . $filename2x[1] :
			$filename2x[0] . '@2x';
	}

	/**
	 * @throws InvalidArgumentException
	 */
	protected function getWidth(object $photo, int $variant): int
	{
		switch ($variant) {
			case self::VARIANT_THUMB:
				return self::THUMBNAIL_DIM;
			case self::VARIANT_THUMB2X:
				return self::THUMBNAIL2X_DIM;
			case self::VARIANT_SMALL:
				return $photo->small_width ?: 0;
			case self::VARIANT_SMALL2X:
				return $photo->small2x_width ?: 0;
			case self::VARIANT_MEDIUM:
				return $photo->medium_width ?: 0;
			case self::VARIANT_MEDIUM2X:
				return $photo->medium2x_width ?: 0;
			case self::VARIANT_ORIGINAL:
				return $photo->width;
			default:
				throw new InvalidArgumentException('Invalid size variant: ' . $variant);
		}
	}

	/**
	 * @throws InvalidArgumentException
	 */
	protected function getHeight(object $photo, int $variant): int
	{
		switch ($variant) {
			case self::VARIANT_THUMB:
				return self::THUMBNAIL_DIM;
			case self::VARIANT_THUMB2X:
				return self::THUMBNAIL2X_DIM;
			case self::VARIANT_SMALL:
				return $photo->small_height ?: 0;
			case self::VARIANT_SMALL2X:
				return $photo->small2x_height ?: 0;
			case self::VARIANT_MEDIUM:
				return $photo->medium_height ?: 0;
			case self::VARIANT_MEDIUM2X:
				return $photo->medium2x_height ?: 0;
			case self::VARIANT_ORIGINAL:
				return $photo->height;
			default:
				throw new InvalidArgumentException('Invalid size variant: ' . $variant);
		}
	}

	/**
	 * @throws InvalidArgumentException
	 */
	protected function hasSizeVariant(object $photo, int $variantType): bool
	{
		if ($variantType == self::VARIANT_ORIGINAL || $variantType == self::VARIANT_THUMB) {
			return true;
		} elseif ($variantType == self::VARIANT_THUMB2X) {
			return (bool) ($photo->thumb2x);
		} else {
			return $this->getWidth($photo, $variantType) != 0;
		}
	}

	/**
	 * A helper function that allows to drop an index if exists.
	 *
	 * @param Blueprint $table
	 * @param string    $indexName
	 *
	 * @throws DBALException
	 */
	private function dropIndexIfExists(Blueprint $table, string $indexName)
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
	 *
	 * @throws DBALException
	 */
	private function dropUniqueIfExists(Blueprint $table, string $indexName)
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
	 *
	 * @throws DBALException
	 */
	private function dropForeignIfExists(Blueprint $table, string $indexName)
	{
		if ($this->driverName === 'sqlite') {
			return;
		}
		$doctrineTable = $this->schemaManager->listTableDetails($table->getTable());
		if ($doctrineTable->hasForeignKey($indexName)) {
			$table->dropForeign($indexName);
		}
	}

	private function generateKey(): string
	{
		// URl-compatible variant of base64 encoding
		// `+` and `/` are replaced by `-` and `_`, resp.
		// The other characters (a-z, A-Z, 0-9) are legal within an URL.
		// As the number of bytes is divisible by 3, no trailing `=` occurs.
		return strtr(base64_encode(random_bytes(3 * self::RANDOM_ID_LENGTH / 4)), '+/', '-_');
	}
}

/**
 * Model class specific for this migration.
 *
 * Migrations are required to be also runnable in the future after the code
 * base will have evolved.
 * To this end, migrations must not rely on a specific implementation of
 * models, because these models may change in the future, but the migration
 * must conduct its task with respect to a table layout which was valid at
 * the time when the migration was written.
 * In conclusion, this implies that migration should not use models but use
 * low-level DB queries when necessary.
 * Unfortunately, we need the `fixTree()` algorithm and there is no
 * implementation which uses low-level DB queries.
 */
class RefactorAlbumModel_AlbumModel extends Model implements Node
{
	use NodeTrait;

	protected $table = 'albums';

	protected $keyType = 'string';

	public $timestamps = false;
}
