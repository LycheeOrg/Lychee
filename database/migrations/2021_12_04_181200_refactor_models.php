<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Doctrine\DBAL\Exception as DBALException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

require_once 'TemporaryModels/RefactorAlbumModel_AlbumModel.php';
require_once 'TemporaryModels/OptimizeTables.php';

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
return new class() extends Migration {
	private ConsoleOutput $output;
	/** @var ProgressBar[] */
	private array $progressBars;
	private ConsoleSectionOutput $msgSection;
	private OptimizeTables $optimize;

	private const SQL_TIMEZONE_NAME = 'UTC';
	private const SQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

	public const THUMBNAIL_DIM = 200;
	public const THUMBNAIL2X_DIM = 400;

	public const VARIANT_ORIGINAL = 0;
	public const VARIANT_MEDIUM2X = 1;
	public const VARIANT_MEDIUM = 2;
	public const VARIANT_SMALL2X = 3;
	public const VARIANT_SMALL = 4;
	public const VARIANT_THUMB2X = 5;
	public const VARIANT_THUMB = 6;

	/**
	 * 2013-11-01 in seconds since epoch.
	 */
	public const BIRTH_OF_LYCHEE = 1383264000;
	public const MAX_SIGNED_32BIT_INT = 2147483647;

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
		$this->output = new ConsoleOutput();
		$this->progressBars = [];
		$this->msgSection = $this->output->section();
		$this->optimize = new OptimizeTables();
	}

	/**
	 * Outputs an error message.
	 *
	 * @param string $msg the message
	 */
	private function printError(string $msg): void
	{
		$this->msgSection->writeln('<error>Error:</error> ' . $msg);
	}

	/**
	 * Outputs a warning.
	 *
	 * @param string $msg the message
	 */
	private function printWarning(string $msg): void
	{
		$this->msgSection->writeln('<comment>Warning:</comment> ' . $msg);
	}

	/**
	 * Outputs an informational message.
	 *
	 * @param string $msg the message
	 */
	private function printInfo(string $msg): void
	{
		$this->msgSection->writeln('<info>Info:</info> ' . $msg);
	}

	/**
	 * Gets the progress bar for the given table.
	 *
	 * The method always returns the same instance of the progress bar for
	 * the same table.
	 * The method creates a new progress bar, when it is called for a new
	 * table name the first time.
	 *
	 * @param string $tableName
	 *
	 * @return ProgressBar
	 */
	private function getProgressBar(string $tableName): ProgressBar
	{
		if (!key_exists($tableName, $this->progressBars)) {
			// Also start a new message section **above** the new progress bar
			// This way the progress bar remains on the bottom in case too
			// many warning/errors are spit out.
			$this->msgSection = $this->output->section();
			$this->progressBars[$tableName] = new ProgressBar($this->output->section());
			$this->progressBars[$tableName]->setFormat('Table \'' . $tableName . '\' %current%/%max% [%bar%] %percent:3s%%');
		}

		return $this->progressBars[$tableName];
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 */
	public function up(): void
	{
		$this->printInfo('Checking consistency of DB');
		$this->ensureDBConsistency();

		Schema::drop('sym_links');

		// Step 1
		// Create tables in correct order so that foreign keys can
		// be created immediately.
		$this->printInfo('Renaming existing tables');
		$this->renameTables();
		$this->printInfo('Creating new tables');
		$this->createUsersTableUp();
		$this->createBaseAlbumTable();
		$this->createAlbumTableUp();
		$this->createTagAlbumTable();
		$this->createUserBaseAlbumTableUp();
		$this->createPhotoTableUp();
		$this->createSizeVariantTableUp();
		$this->createSymLinkTableUp();
		$this->createRemainingForeignConstraints();
		$this->createWebAuthnTableUp();
		$this->createPageTableUp();
		$this->createPageContentTableUp();
		$this->createLogTableUp();

		// Step 2
		// Happy copying :(
		DB::beginTransaction();
		$this->printInfo('Start copying ...');
		$this->upgradeCopy();
		$this->copyStructurallyUnchangedTables();
		$this->printInfo('Finished copying');
		$this->printInfo('Upgrading configuration');
		$this->upgradeConfig();
		DB::commit();

		// Step 3
		$this->printInfo('Dropping old tables');
		$this->dropTemporaryTablesUp();
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function down(): void
	{
		Schema::drop('sym_links');

		// Step 1
		// Create tables in correct order so that foreign keys can
		// be created immediately.
		$this->printInfo('Renaming existing tables');
		$this->renameTables();
		$this->printInfo('Creating new tables');
		$this->createUsersTableDown();
		$this->createAlbumTableDown();
		$this->createUserAlbumTableDown();
		$this->createPhotoTableDown();
		$this->createSymLinkTableDown();
		$this->createWebAuthnTableDown();
		$this->createPageTableDown();
		$this->createPageContentTableDown();
		$this->createLogTableDown();

		// Step 2
		// Happy copying :(
		DB::beginTransaction();
		$this->printInfo('Start copying ...');
		$this->downgradeCopy();
		$this->copyStructurallyUnchangedTables();
		$this->printInfo('Finished copying');
		$this->printInfo('Downgrading configuration');
		$this->downgradeConfig();
		DB::commit();

		// Step 3
		$this->printInfo('Dropping old tables');
		$this->dropTemporaryTablesDown();
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
			$this->optimize->dropForeignIfExists($table, 'albums_owner_id_foreign');
			// We must remove any foreign link from `albums` to `photos` to
			// break up circular dependencies.
			$this->optimize->dropForeignIfExists($table, 'albums_cover_id_foreign');
			$this->optimize->dropForeignIfExists($table, 'albums_parent_id_foreign');
			$this->optimize->dropIndexIfExists($table, 'albums__lft__rgt_index');
		});
		Schema::rename('albums', 'albums_tmp');
		Schema::table('photos', function (Blueprint $table) {
			$this->optimize->dropForeignIfExists($table, 'photos_album_id_foreign');
			$this->optimize->dropForeignIfExists($table, 'photos_owner_id_foreign');
			$this->optimize->dropIndexIfExists($table, 'photos_created_at_index');
			$this->optimize->dropIndexIfExists($table, 'photos_updated_at_index');
			$this->optimize->dropIndexIfExists($table, 'photos_taken_at_index');
			$this->optimize->dropIndexIfExists($table, 'photos_original_checksum_index');
			$this->optimize->dropIndexIfExists($table, 'photos_checksum_index');
			$this->optimize->dropIndexIfExists($table, 'photos_live_photo_content_id_index');
			$this->optimize->dropIndexIfExists($table, 'photos_livephotocontentid_index');
			$this->optimize->dropIndexIfExists($table, 'photos_live_photo_checksum_index');
			$this->optimize->dropIndexIfExists($table, 'photos_livephotochecksum_index');
			$this->optimize->dropIndexIfExists($table, 'photos_is_public_index');
			$this->optimize->dropIndexIfExists($table, 'photos_is_starred_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_taken_at_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_created_at_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_public_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_type_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_created_at_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_taken_at_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_is_public_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_type_index');
		});
		Schema::rename('photos', 'photos_tmp');
		Schema::table('web_authn_credentials', function (Blueprint $table) {
			$this->optimize->dropForeignIfExists($table, 'web_authn_credentials_user_id_foreign');
		});
		Schema::rename('web_authn_credentials', 'web_authn_credentials_tmp');
		Schema::table('users', function (Blueprint $table) {
			$this->optimize->dropUniqueIfExists($table, 'users_username_unique');
			$this->optimize->dropUniqueIfExists($table, 'users_email_unique');
		});
		Schema::rename('users', 'users_tmp');
		$this->renamePageContentTable();
		Schema::rename('pages', 'pages_tmp');
		Schema::rename('logs', 'logs_tmp');
	}

	/**
	 * Drops temporary tables which have been created by
	 * {@link RefactorAlbumModel::renameTables()} or have become unnecessary.
	 *
	 * The order is important to avoid error due to unsatisfied foreign
	 * constraints.
	 */
	private function dropTemporaryTablesUp(): void
	{
		Schema::drop('user_album');
		// We must remove any foreign link from `albums` to `photos` to
		// break up circular dependencies.
		DB::table('albums_tmp')->update(['cover_id' => null]);
		Schema::drop('photos_tmp');
		Schema::drop('albums_tmp');
		Schema::drop('web_authn_credentials_tmp');
		Schema::drop('users_tmp');
		Schema::drop('page_contents_tmp');
		Schema::drop('pages_tmp');
		Schema::drop('logs_tmp');
	}

	/**
	 * Drops temporary tables which have been created by
	 * {@link RefactorAlbumModel::renameTables()} or have become unnecessary.
	 *
	 * The order is important to avoid error due to unsatisfied foreign
	 * constraints.
	 */
	private function dropTemporaryTablesDown(): void
	{
		Schema::drop('user_base_album');
		Schema::drop('size_variants');
		// We must remove any foreign link from `albums` to `photos` to
		// break up circular dependencies.
		DB::table('albums_tmp')->update(['cover_id' => null]);
		Schema::drop('photos_tmp');
		Schema::drop('albums_tmp');
		Schema::drop('tag_albums');
		Schema::drop('base_albums');
		Schema::drop('web_authn_credentials_tmp');
		Schema::drop('users_tmp');
		Schema::drop('page_contents_tmp');
		Schema::drop('pages_tmp');
		Schema::drop('logs_tmp');
	}

	/**
	 * Creates the new table `users` with improved attribute names.
	 *
	 * Note: Actually, renaming of the attributes `lock` to `is_locked` and
	 * `upload` to `may_upload` should not be part of this migration, because
	 * it is unrelated to the refactored, new architecture.
	 * However, there will be a subsequent PR which aims at making the JSON
	 * API more consistent and in this context this migration make sense.
	 * Unfortunately, SQLite does not support renaming of columns in place.
	 * Under the hood, SQLite drops the entire table and re-creates it.
	 * But this fails, if there are foreign key constraints from other tables
	 * to `users`.
	 * Eventually, we would end up with re-creating the whole DB again. :-(
	 * Hence, we bring forward this migration when we re-create the whole DB
	 * anyway.
	 */
	private function createUsersTableUp(): void
	{
		Schema::create('users', function (Blueprint $table) {
			$table->increments('id');
			$table->dateTime('created_at', 6)->nullable(false);
			$table->dateTime('updated_at', 6)->nullable(false);
			$table->string('username', 100)->nullable(false)->unique();
			$table->string('password', 100)->nullable(true);
			$table->string('email', 100)->nullable()->unique();
			$table->boolean('may_upload')->nullable(false)->default(false);
			$table->boolean('is_locked')->nullable(false)->default(false);
			$table->rememberToken();
		});
	}

	/**
	 * Creates the old table `users`.
	 */
	private function createUsersTableDown(): void
	{
		Schema::create('users', function (Blueprint $table) {
			$table->increments('id');
			$table->dateTime('created_at')->nullable(false);
			$table->dateTime('updated_at')->nullable(false);
			$table->string('username', 100)->nullable(false)->unique();
			$table->string('password', 100)->nullable(true);
			$table->string('email', 100)->nullable()->unique();
			$table->boolean('upload')->nullable(false)->default(false);
			$table->boolean('lock')->nullable(false)->default(false);
			$table->rememberToken();
		});
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
			$table->dateTime('created_at', 6)->nullable(false);
			$table->dateTime('updated_at', 6)->nullable(false);
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
			$table->dateTime('created_at', 6)->nullable(false);
			$table->dateTime('updated_at', 6)->nullable(false);
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
			$table->dateTime('taken_at', 6)->nullable(true)->default(null)->comment('relative to UTC');
			$table->string('taken_at_orig_tz', 31)->nullable(true)->default(null)->comment('the timezone at which the photo has originally been taken');
			$table->string('type', 30)->nullable(false);
			$table->unsignedBigInteger('filesize')->nullable(false)->default(0);
			$table->string('checksum', 40)->nullable(false);
			$table->string('original_checksum', 40)->nullable(false);
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
			$table->index('original_checksum');
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
			$table->index(['album_id', 'type']);
			// These indices are needed to efficiently retrieve the covers of
			// albums acc. to different sorting criteria
			// Note, that covers are always sorted acc. to `is_starred` first.
			$table->index(['album_id', 'is_starred', 'created_at']);
			$table->index(['album_id', 'is_starred', 'taken_at']);
			$table->index(['album_id', 'is_starred', 'is_public']);
			$table->index(['album_id', 'is_starred', 'type']);
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
			$table->dateTime('created_at', 6)->nullable(false);
			$table->dateTime('updated_at', 6)->nullable(false);
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

	private function createLogTable(int $precision): void
	{
		Schema::create('logs', function (Blueprint $table) use ($precision) {
			$table->bigIncrements('id');
			$table->dateTime('created_at', $precision)->nullable(false);
			$table->dateTime('updated_at', $precision)->nullable(false);
			$table->string('type', 11);
			$table->string('function', 100);
			$table->integer('line');
			$table->text('text');
		});
	}

	private function createLogTableUp(): void
	{
		$this->createLogTable(6);
	}

	private function createLogTableDown(): void
	{
		$this->createLogTable(0);
	}

	private function createPageTable(int $precision): void
	{
		Schema::create('pages', function (Blueprint $table) use ($precision) {
			$table->increments('id');
			$table->dateTime('created_at', $precision)->nullable(false);
			$table->dateTime('updated_at', $precision)->nullable(false);
			$table->string('title', 150)->default('');
			$table->string('menu_title', 100)->default('');
			$table->boolean('in_menu')->default(false);
			$table->boolean('enabled')->default(false);
			$table->string('link', 150)->default('');
			$table->integer('order')->default(0);
		});
	}

	private function createPageTableUp(): void
	{
		$this->createPageTable(6);
	}

	private function createPageTableDown(): void
	{
		$this->createPageTable(0);
	}

	private function createPageContentTable(string $tableName, int $precision): void
	{
		Schema::create($tableName, function (Blueprint $table) use ($precision) {
			$table->increments('id');
			$table->dateTime('created_at', $precision)->nullable(false);
			$table->dateTime('updated_at', $precision)->nullable(false);
			$table->unsignedInteger('page_id');
			$table->text('content');
			$table->string('class', 150);
			$table->enum('type', ['div', 'img']);
			$table->integer('order')->default(0);
			// Indices
			$table->foreign('page_id')
				->references('id')->on('pages')
				->onDelete('cascade');
		});
	}

	private function createPageContentTableUp(): void
	{
		$this->createPageContentTable('page_contents', 6);
	}

	private function createPageContentTableDown(): void
	{
		$this->createPageContentTable('page_contents', 0);
	}

	/**
	 * Renames table `page_content` to `page_content_tmp` using a work-around.
	 *
	 * Ideally, we would simply use
	 * `Schema::rename('page_content', 'page_content_tmp')`
	 * in {@link RefactorModels::renameTables()} as for any other table.
	 * Unfortunately, a bug in Laravel/Eloquent does not allow this, so we
	 * need to create a table `page_contents_tmp` copy everything into that
	 * table, and drop `page_contents`.
	 * (And yes, we do it the other way around just some minutes later.)
	 * Yikes!
	 *
	 * The cause of the problem is that the table uses the non-SQL type
	 * `enum` (see `CreatePageContentsTable::up` in
	 * `2019_02_21_114408_create_page_contents_table.php`).
	 * Under the hood, Laravel/Eloquent registers this proprietary extension
	 * with the DBAL (database abstraction layer) and a callback ensures
	 * that this type gets properly translated into an actual SQL type
	 * whenever the DBAL encounters this type depending on the SQL backend:
	 *
	 *  - MySQL: `ENUM`
	 *  - PostgreSQL: `VARCHAR` with a `CHECK`-constraint
	 *  - SQLite: `VARCHAR`
	 *
	 * However, Laravel/Eloquent only registers this type extension for
	 * table creation.
	 * (That is actually a known bug which Laravel/Eloquent refuses to fix.)
	 * As a result, the DBAL will bail out with an exception whenever it tries
	 * to modify the table schema in the slightest way (rename the table,
	 * drop/add/rename a column, change a column) even if the modification
	 * does not alter the enum-column itself, because it will topple over an
	 * unknown type.
	 * Essentially, the table schema becomes immutable.
	 * The only possible action left which does not trigger an exception is to
	 * drop the table.
	 */
	private function renamePageContentTable(): void
	{
		$nowString = Carbon::now(self::SQL_TIMEZONE_NAME)->format(self::SQL_DATETIME_FORMAT);

		$this->createPageContentTable('page_contents_tmp', 0);
		$pageContents = DB::table('page_contents')->get();
		foreach ($pageContents as $pageContent) {
			DB::table('page_contents_tmp')->insert([
				'id' => $pageContent->id,
				'created_at' => $pageContent->created_at ?? $nowString,
				'updated_at' => $pageContent->updated_at ?? $nowString,
				'page_id' => $pageContent->page_id,
				'content' => $pageContent->content,
				'class' => $pageContent->class,
				'type' => $pageContent->type,
				'order' => $pageContent->order,
			]);
		}
		Schema::drop('page_contents');
	}

	private function createWebAuthnTable(int $precision): void
	{
		Schema::create('web_authn_credentials', function (Blueprint $table) use ($precision) {
			$table->string('id', 255);
			$table->dateTime('created_at', $precision)->nullable(false);
			$table->dateTime('updated_at', $precision)->nullable(false);
			$table->dateTime('disabled_at', $precision)->nullable(true);
			$table->unsignedInteger('user_id')->nullable(false);
			$table->string('name')->nullable();
			$table->string('type', 16);
			$table->json('transports');
			$table->json('attestation_type');
			$table->json('trust_path');
			$table->uuid('aaguid');
			$table->binary('public_key');
			$table->unsignedInteger('counter')->default(0);
			$table->uuid('user_handle')->nullable();
			// Indices
			$table->primary(['id', 'user_id']);
			$table->foreign('user_id')
				->references('id')->on('users')
				->cascadeOnDelete();
		});
	}

	private function createWebAuthnTableUp(): void
	{
		$this->createWebAuthnTable(6);
	}

	private function createWebAuthnTableDown(): void
	{
		$this->createWebAuthnTable(0);
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
			$table->foreign('cover_id')
				->references('id')->on('photos')
				->onUpdate('CASCADE')
				->onDelete('SET NULL');
		});
	}

	/**
	 * @throws InvalidArgumentException
	 */
	private function upgradeCopy(): void
	{
		$pgBar = $this->getProgressBar('users');
		$users = DB::table('users_tmp')->get();
		$pgBar->setMaxSteps($users->count());
		foreach ($users as $user) {
			$pgBar->advance();
			DB::table('users')->insert([
				'id' => $user->id,
				'created_at' => $user->created_at,
				'updated_at' => $user->updated_at,
				'username' => $user->username,
				'password' => $user->password,
				'email' => $user->email,
				'may_upload' => $user->upload,
				'is_locked' => $user->lock,
				'remember_token' => $user->remember_token,
			]);
		}

		// Ordering by `_lft` is important, because we must copy parent
		// albums first.
		// Otherwise, foreign key constraint to `parent_id` may fail.
		$pgBar = $this->getProgressBar('albums');
		$albums = DB::table('albums_tmp')->orderBy('_lft')->lazyById();
		$pgBar->setMaxSteps($albums->count());
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
			$pgBar->advance();
			$newAlbumID = $this->generateKey();
			$this->albumIDCache[strval($album->id)] = $newAlbumID;

			DB::table('base_albums')->insert([
				'id' => $newAlbumID,
				'legacy_id' => $album->id,
				'created_at' => $this->calculateBestCreatedAt($album->id, $album->created_at),
				'updated_at' => $album->updated_at,
				'title' => $album->title,
				'description' => empty($album->description) ? null : $album->description,
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

		$pgBar = $this->getProgressBar('user_base_album');
		$userAlbumRelations = DB::table('user_album')->lazyById();
		$pgBar->setMaxSteps($userAlbumRelations->count());
		foreach ($userAlbumRelations as $userAlbumRelation) {
			$pgBar->advance();
			DB::table('user_base_album')->insert([
				'id' => $userAlbumRelation->id,
				'user_id' => $userAlbumRelation->user_id,
				'base_album_id' => $this->albumIDCache[strval($userAlbumRelation->album_id)],
			]);
		}

		$pgBar = $this->getProgressBar('photos');
		$photos = DB::table('photos_tmp')->lazyById();
		$pgBar->setMaxSteps($photos->count());
		foreach ($photos as $photo) {
			$pgBar->advance();
			$newPhotoID = $this->generateKey();
			$this->photoIDCache[strval($photo->id)] = $newPhotoID;

			DB::table('photos')->insert([
				'id' => $newPhotoID,
				'legacy_id' => $photo->id,
				'created_at' => $this->calculateBestCreatedAt($photo->id, $photo->created_at),
				'updated_at' => $photo->updated_at,
				'owner_id' => $photo->owner_id,
				'album_id' => $photo->album_id ? $this->albumIDCache[strval($photo->album_id)] : null,
				'title' => $photo->title,
				'description' => empty($photo->description) ? null : $photo->description,
				'tags' => empty($photo->tags) ? null : $photo->tags,
				'license' => $photo->license,
				'is_public' => $photo->public,
				'is_starred' => $photo->star,
				'iso' => empty($photo->iso) ? null : $photo->iso,
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
				'original_checksum' => $photo->checksum,
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
		}

		// Restore explicit covers of albums
		$pgBar = $this->getProgressBar('albums (covered)');
		$coveredAlbums = DB::table('albums_tmp')
			->whereNotNull('cover_id')
			->where('smart', '=', false)
			->lazyById();
		$pgBar->setMaxSteps($coveredAlbums->count());
		foreach ($coveredAlbums as $coveredAlbum) {
			$pgBar->advance();
			DB::table('albums')
				->where('id', '=', $this->albumIDCache[strval($coveredAlbum->id)])
				->update(['cover_id' => $this->photoIDCache[strval($coveredAlbum->cover_id)]]);
		}
	}

	/**
	 * @throws InvalidArgumentException
	 */
	private function downgradeCopy(): void
	{
		$pgBar = $this->getProgressBar('users');
		$users = DB::table('users_tmp')->get();
		$pgBar->setMaxSteps($users->count());
		foreach ($users as $user) {
			$pgBar->advance();
			DB::table('users')->insert([
				'id' => $user->id,
				'created_at' => $user->created_at,
				'updated_at' => $user->updated_at,
				'username' => $user->username,
				'password' => $user->password,
				'email' => $user->email,
				'upload' => $user->may_upload,
				'lock' => $user->is_locked,
				'remember_token' => $user->remember_token,
			]);
		}

		$pgBar = $this->getProgressBar('base_albums');
		$baseAlbums = DB::table('base_albums')->lazyById();
		$pgBar->setMaxSteps($baseAlbums->count());
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
			$pgBar->advance();
			$legacyAlbumID = intval($oldBaseAlbum->legacy_id);
			$this->albumIDCache[$oldBaseAlbum->id] = $legacyAlbumID;

			DB::table('albums')->insert([
				'id' => $legacyAlbumID,
				'created_at' => $oldBaseAlbum->created_at,
				'updated_at' => $oldBaseAlbum->updated_at,
				'title' => $oldBaseAlbum->title,
				'description' => empty($oldBaseAlbum->description) ? '' : $oldBaseAlbum->description,
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
		$pgBar = $this->getProgressBar('albums');
		$albums = DB::table('albums_tmp')->orderBy('_lft')->lazyById();
		$pgBar->setMaxSteps($albums->count());
		foreach ($albums as $album) {
			$pgBar->advance();
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

		$pgBar = $this->getProgressBar('tag_albums');
		$tagAlbums = DB::table('tag_albums')->lazyById();
		$pgBar->setMaxSteps($tagAlbums->count());
		foreach ($tagAlbums as $tagAlbum) {
			$pgBar->advance();
			DB::table('albums')
				->where('id', '=', $this->albumIDCache[$tagAlbum->id])
				->update([
					'smart' => true,
					'showtags' => $tagAlbum->show_tags,
				]);
		}

		RefactorAlbumModel_AlbumModel::query()->fixTree();

		$pgBar = $this->getProgressBar('user_album');
		$userBaseAlbumRelations = DB::table('user_base_album')->lazyById();
		$pgBar->setMaxSteps($userBaseAlbumRelations->count());
		foreach ($userBaseAlbumRelations as $userBaseAlbumRelation) {
			$pgBar->advance();
			DB::table('user_album')->insert([
				'id' => $userBaseAlbumRelation->id,
				'user_id' => $userBaseAlbumRelation->user_id,
				'album_id' => $this->albumIDCache[$userBaseAlbumRelation->base_album_id],
			]);
		}

		$pgBar = $this->getProgressBar('photos');
		$photos = DB::table('photos_tmp')->lazyById();
		$pgBar->setMaxSteps($photos->count());
		foreach ($photos as $photo) {
			$pgBar->advance();
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
				'iso' => empty($photo->iso) ? '' : $photo->iso,
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
				'checksum' => $photo->original_checksum,
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
			if ($originalSizeVariant->type !== self::VARIANT_ORIGINAL) {
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
					$sizeVariant->type === self::VARIANT_THUMB2X ||
					$sizeVariant->type === self::VARIANT_SMALL2X ||
					$sizeVariant->type === self::VARIANT_MEDIUM2X
				) {
					$expectedFilename = $expectedBasename . '@2x' . $fileExtension;
				} else {
					$expectedFilename = $expectedBasename . $fileExtension;
				}
				$expectedPathPrefix = self::VARIANT_2_PATH_PREFIX[$sizeVariant->type] . '/';
				if ($sizeVariant->type === self::VARIANT_ORIGINAL && $this->isRaw($photo)) {
					$expectedPathPrefix = 'raw/';
				}
				$expectedShortPath = $expectedPathPrefix . $expectedFilename;

				// Ensure that the size variant is stored at the location which
				// is expected acc. to the old naming scheme
				if ($sizeVariant->short_path !== $expectedShortPath) {
					try {
						Storage::move($sizeVariant->short_path, $expectedShortPath);
					} catch (\Throwable $e) {
						// sic! just ignore
						// This exception is thrown if there are duplicate
						// photos which point to the same physical file.
						// Then the file is renamed when the first occurrence
						// of those duplicates is processed and subsequent,
						// failing attempts to rename the file must be ignored.
					}
				}

				if ($sizeVariant->type === self::VARIANT_THUMB2X) {
					$photoAttributes['thumb2x'] = true;
				} elseif ($sizeVariant->type === self::VARIANT_THUMB) {
					$photoAttributes['thumbUrl'] = $expectedFilename;
				} else {
					if ($sizeVariant->type === self::VARIANT_ORIGINAL) {
						$photoAttributes['url'] = $expectedFilename;
					}
					$photoAttributes[self::VARIANT_2_WIDTH_ATTRIBUTE[$sizeVariant->type]] = $sizeVariant->width;
					$photoAttributes[self::VARIANT_2_HEIGHT_ATTRIBUTE[$sizeVariant->type]] = $sizeVariant->height;
				}
			}

			DB::table('photos')->insert($photoAttributes);
		}

		// Restore explicit covers of albums
		$pgBar = $this->getProgressBar('albums (covered)');
		$coveredAlbums = DB::table('albums_tmp')
			->whereNotNull('cover_id')
			->lazyById();
		$pgBar->setMaxSteps($coveredAlbums->count());
		foreach ($coveredAlbums as $coveredAlbum) {
			$pgBar->advance();
			DB::table('albums')
				->where('id', '=', $this->albumIDCache[$coveredAlbum->id])
				->update(['cover_id' => $this->photoIDCache[$coveredAlbum->cover_id]]);
		}
	}

	/**
	 * Copies those table which have not changed structurally, but whose
	 * date/time precision has changed.
	 */
	private function copyStructurallyUnchangedTables(): void
	{
		$pgBar = $this->getProgressBar('web_authn_credentials');
		$credentials = DB::table('web_authn_credentials_tmp')->get();
		$pgBar->setMaxSteps($credentials->count());
		foreach ($credentials as $credential) {
			$pgBar->advance();
			DB::table('web_authn_credentials')->insert([
				'id' => $credential->id,
				'created_at' => $credential->created_at,
				'updated_at' => $credential->updated_at,
				'disabled_at' => $credential->disabled_at,
				'user_id' => $credential->user_id,
				'name' => $credential->name,
				'type' => $credential->type,
				'transports' => $credential->transports,
				'attestation_type' => $credential->attestation_type,
				'trust_path' => $credential->trust_path,
				'aaguid' => $credential->aaguid,
				'public_key' => $credential->public_key,
				'counter' => $credential->counter,
				'user_handle' => $credential->user_handle,
			]);
		}

		$pgBar = $this->getProgressBar('pages');
		$pages = DB::table('pages_tmp')->get();
		$pgBar->setMaxSteps($pages->count());
		foreach ($pages as $page) {
			$pgBar->advance();
			DB::table('pages')->insert([
				'id' => $page->id,
				'created_at' => $page->created_at,
				'updated_at' => $page->updated_at,
				'title' => $page->title,
				'menu_title' => $page->menu_title,
				'in_menu' => $page->in_menu,
				'enabled' => $page->enabled,
				'link' => $page->link,
				'order' => $page->order,
			]);
		}

		$pgBar = $this->getProgressBar('page_contents');
		$pageContents = DB::table('page_contents_tmp')->get();
		$pgBar->setMaxSteps($pageContents->count());
		foreach ($pageContents as $pageContent) {
			$pgBar->advance();
			DB::table('page_contents')->insert([
				'id' => $pageContent->id,
				'created_at' => $pageContent->created_at,
				'updated_at' => $pageContent->updated_at,
				'page_id' => $pageContent->page_id,
				'content' => $pageContent->content,
				'class' => $pageContent->class,
				'type' => $pageContent->type,
				'order' => $pageContent->order,
			]);
		}

		$pgBar = $this->getProgressBar('logs');
		$logs = DB::table('logs_tmp')->get();
		$pgBar->setMaxSteps($logs->count());
		foreach ($logs as $log) {
			$pgBar->advance();
			DB::table('logs')->insert([
				'id' => $log->id,
				'created_at' => $log->created_at,
				'updated_at' => $log->updated_at,
				'type' => $log->type,
				'function' => $log->function,
				'line' => $log->line,
				'text' => $log->text,
			]);
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
		DB::table('configs')
			->insert([
				'key' => 'legacy_id_redirection',
				'value' => '1',
				'cat' => 'config',
				'confidentiality' => 0,
				'type_range' => '0|1',
				'description' => 'Enables/disables the redirection support for legacy IDs',
			]);
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
		DB::table('configs')
			->where('key', '=', 'legacy_id_redirection')
			->delete();
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
		return $photo->type === 'raw';
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
		if ($variantType === self::VARIANT_ORIGINAL || $variantType === self::VARIANT_THUMB) {
			return true;
		} elseif ($variantType === self::VARIANT_THUMB2X) {
			return (bool) ($photo->thumb2x);
		} else {
			return $this->getWidth($photo, $variantType) !== 0;
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

	/**
	 * Converts a legacy ID to a Carbon instance.
	 *
	 * The method handles 32bit and 64bit integers with second and
	 * 1/10 millisecond resolution.
	 *
	 * @param int $id
	 *
	 * @return Carbon
	 *
	 * @throws OutOfBoundsException thrown, if `$id` is out of reasonable bounds
	 */
	private function convertLegacyIdToTime(int $id): Carbon
	{
		// Typically, the legacy ID should have either
		//
		//  - 10 digits for 32bit platforms, or
		//  - 14 digits (for 64bit platforms).
		//
		// On 32bit platforms, the ID indicates the creation date in
		// seconds since epoch.
		// On 64bit platforms, the ID indicates the creation date in
		// 1/10 of microseconds since epoch.
		// This means we have four decimal digits of additional precision.
		//
		// Unfortunately, due to a bug in Lychee at some time, trailing zeros
		// were stripped off.
		// This means, the 2-digit number 16 might actually indicate
		// the timestamp 1600000000 (Sep 13th, 2020) on a 32bit platform.
		// Likewise, the 12-digit number 162033368845 might actually indicate
		// the timestamp 16203336884500 (May 6h, 2021) on a 64bit platform.
		//
		// However, in any case we know that the integer part (measured in
		// seconds since epoch) must have 10 digits.
		// Any other value would not be reasonable, as 999,999,999 is a date
		// in 2001 long before the birth of Lychee.
		// Also, `self::BIRTH_OF_LYCHEE` is approx. one half of
		// `self::MAX_SIGNED_32BIT_INT` (Jan 19th, 2038) which is far in the
		// future.
		// So, we can multiply/divide the id by ten for numbers which are too
		// small/large and be safe that there is at most only a single
		// value in the reasonable interval.
		// For 32bit platforms we must take care of overflows for the
		// multiplication, i.e. we must check for <MAX_SIGNED_32BIT_INT/10
		// **before** the multiplication.

		// This is Oct 21st, 1976, so it is also smaller than `self::BIRTH_OF_LYCHEE`
		if ($id < self::MAX_SIGNED_32BIT_INT / 10 - 1) {
			$id *= 10;
		}

		// This will never be true for 32bit platforms, but might be true
		// for 64bit platforms with high-precision timestamps
		if ($id > self::MAX_SIGNED_32BIT_INT) {
			$id = (float) $id;
			while ($id >= self::MAX_SIGNED_32BIT_INT) {
				$id /= 10.0;
			}
		}

		if ($id <= self::BIRTH_OF_LYCHEE) {
			throw new \OutOfBoundsException('ID-based creation time is out of reasonable bounds');
		}

		return Carbon::createFromTimestampUTC($id);
	}

	/**
	 * Calculates the best creation time of a DB record.
	 *
	 * The method takes the (legacy) ID and the alleged creation date
	 * (as an SQL string) and returns an SQL string of the "best" creation
	 * time.
	 * The best creation time is either the converted, legacy ID as it
	 * provides a higher accuracy, or the original creation time, if the
	 * time based on the ID and the original creation time differ by more
	 * than 30 seconds.
	 * The latter is a safety measure in case someone has internally tweaked
	 * the IDs or the creation date, or if something is completely wrong
	 * with the timezone settings.
	 *
	 * @param int    $legacyID     the legacy ID of the record
	 * @param string $sqlCreatedAt the original creation time of the record (as an SQL string)
	 *
	 * @return string the improved creation time of the record (as an SQL string)
	 */
	private function calculateBestCreatedAt(int $legacyID, string $sqlCreatedAt): string
	{
		$result = $sqlCreatedAt;

		try {
			try {
				$originalCreatedAt = Carbon::createFromFormat(
					'Y-m-d H:i:s.u',
					$sqlCreatedAt,
					'UTC'
				);
			} catch (InvalidFormatException $e) {
				$originalCreatedAt = Carbon::createFromFormat(
					'Y-m-d H:i:s',
					$sqlCreatedAt,
					'UTC'
				);
			}

			$idBasesCreatedAt = $this->convertLegacyIdToTime($legacyID);
			$diff = $originalCreatedAt->diff($idBasesCreatedAt, true);

			if ($diff->y === 0 || $diff->m === 0 || $diff->d === 0 || $diff->h === 0 || $diff->i === 0 || $diff->s < 30) {
				$result = $idBasesCreatedAt->format('Y-m-d H:i:s.u');
			} else {
				throw new \RangeException('ID-based creation time and original creation time differ more than 30s');
			}
		} catch (\RangeException $e) {
			$this->printWarning(
				'Model ID ' . $legacyID . ' - ' .
					class_basename($e) . ' - ' . $e->getMessage()
			);
		} catch (\Throwable $e) {
			$this->printError(
				'Model ID ' . $legacyID . ' - ' .
					class_basename($e) . ' - ' . $e->getMessage()
			);
		}

		return $result;
	}

	/**
	 * Ensures the consistency of the DB on the upgrade path.
	 *
	 * The method checks the DB for consistency.
	 * In case of errors, the method either
	 *
	 *  1. automatically corrects the problem if the fix is easy and prints
	 *     a warning, or
	 *  2. bails out with an exception and prints an error message, if the
	 *     problem needs manual attention.
	 *
	 * The method either returns or bails out with an exception.
	 *
	 * @throws RuntimeException         thrown, if DB is inconsistent
	 * @throws InvalidArgumentException
	 */
	private function ensureDBConsistency(): void
	{
		$checkRelation = function (
			string $modelName,
			string $table,
			string $column,
			string $foreignModelName,
			string $foreignTable,
			string $fixMethod = '',
		): bool {
			$missing = DB::table($table)
				->whereNotIn($column, function (Builder $q) use ($foreignTable) {
					$q->from($foreignTable)->select('id');
				})
				->select('id', $column)
				->get();

			foreach ($missing as $m) {
				$msg = 'Found ' . $modelName .
					' with ID ' . $m->id .
					' which refers to non-existing ' . $foreignModelName .
					' with ID ' . $m->{$column};
				if (empty($fixMethod)) {
					$this->printError($msg);
				} else {
					$this->printWarning($msg);
				}
			}

			if ($missing->isEmpty()) {
				return true;
			}

			$fixQuery = DB::table($table)->whereIn('id', $missing->pluck('id'));

			switch ($fixMethod) {
				case 'nullify':
					$this->printInfo('Nullifying the affected relations from ' . $modelName . 's to ' . $foreignModelName . 's');
					$fixQuery->update([$column => null]);

					return true;
				case 'zeroize':
					$this->printInfo('Zeroizing the affected relations from ' . $modelName . 's to ' . $foreignModelName . 's');
					$fixQuery->update([$column => 0]);

					return true;
				case 'delete':
					$this->printInfo('Deleting the affected ' . $modelName . 's');
					$fixQuery->delete();

					return true;
				default:
					$this->printInfo('Error is not automatically fixable');

					return false;
			}
		};

		// If the owner of an album is missing, assign it to the admin user
		$isConsistent = $checkRelation('album', 'albums', 'owner_id', 'user', 'users', 'zeroize');
		// Move orphaned albums to the top-level
		$isConsistent &= $checkRelation('album', 'albums', 'parent_id', 'parent album', 'albums', 'nullify');
		// If the cover of an album is missing, unset the cover
		$isConsistent &= $checkRelation('album', 'albums', 'cover_id', 'cover photo', 'photos', 'nullify');
		// Delete orphaned shares
		$isConsistent &= $checkRelation('share', 'user_album', 'user_id', 'user', 'users', 'delete');
		$isConsistent &= $checkRelation('share', 'user_album', 'album_id', 'album', 'albums', 'delete');
		// If the owner of a photo is missing, assign it to the admin user
		$isConsistent &= $checkRelation('photo', 'photos', 'owner_id', 'user', 'users', 'zeroize');
		// If the album of a photo is missing, assign it to root (unsorted) album
		$isConsistent &= $checkRelation('photo', 'photos', 'album_id', 'album', 'albums', 'nullify');
		// Delete orphaned WebAuthn credentials
		$isConsistent &= $checkRelation('web authentication credential', 'web_authn_credentials', 'user_id', 'user', 'users', 'delete');
		// There is no obvious fix for orphaned page content
		$isConsistent &= $checkRelation('page content', 'page_contents', 'page_id', 'page', 'pages');

		// As we might have moved orphaned albums to the top,
		// we need to fix the tree.
		// Even if we did not move any album, fixing the tree before
		// the migration does not harm as users might have fiddled with their
		// DB without taking care of
		// `_lft` and `_rgt`
		RefactorAlbumModel_AlbumModel::query()->fixTree();

		if (!$isConsistent) {
			$this->printError('Your database is inconsistent and not fit for migration. Please fix your DB manually first.');
			throw new \RuntimeException('Inconsistent DB');
		}
	}
};
