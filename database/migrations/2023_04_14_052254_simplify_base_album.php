<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require_once 'TemporaryModels/OptimizeTables.php';

return new class() extends Migration {
	private const TABLE_NAME = 'access_permissions';

	private const USER_ID = 'user_id';
	private const BASE_ALBUM_ID = 'base_album_id';
	private const IS_LINK_REQUIRED = 'is_link_required';
	private const PASSWORD = 'password';
	private const GRANTS_FULL_PHOTO_ACCESS = 'grants_full_photo_access';
	private const GRANTS_DOWNLOAD = 'grants_download';

	private const IS_PUBLIC = 'is_public';

	public const RANDOM_ID_LENGTH = 24;

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::dropIfExists('user_base_album');
		$this->dropColumnsBaseAlbumTable();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		$this->createUserBaseAlbumTable();
		DB::transaction(fn () => $this->populateUserBaseAlbumTable());

		$this->createBaseAlbumTable();
		DB::transaction(fn () => $this->populateBaseAlbumTable());

		$optimize = new OptimizeTables();
		$optimize->exec();
	}

	private function dropColumnsBaseAlbumTable(): void
	{
		Schema::table('base_albums', function ($table) {
			$table->dropColumn(self::IS_PUBLIC);
		});
		Schema::table('base_albums', function ($table) {
			$table->dropColumn(self::GRANTS_FULL_PHOTO_ACCESS);
		});
		Schema::table('base_albums', function ($table) {
			$table->dropColumn(self::GRANTS_DOWNLOAD);
		});
		Schema::table('base_albums', function ($table) {
			$table->dropColumn(self::IS_LINK_REQUIRED);
		});
		Schema::table('base_albums', function ($table) {
			$table->dropColumn(self::PASSWORD);
		});
	}

	private function createUserBaseAlbumTable(): void
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

	private function populateUserBaseAlbumTable(): void
	{
		$shared = DB::table(self::TABLE_NAME)->whereNotNull('user_id')->get();
		foreach ($shared as $share) {
			DB::table('user_base_album')->
				insert([
					self::USER_ID => $share->user_id,
					self::BASE_ALBUM_ID => $share->base_album_id,
				]);
		}
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
			$table->boolean('is_public')->nullable(false)->default(false);
			$table->boolean('grants_full_photo')->nullable(false)->default(true);
			$table->boolean('requires_link')->nullable(false)->default(false);
			$table->boolean('is_downloadable')->nullable(false)->default(false);
			$table->boolean('is_share_button_visible')->nullable(false)->default(false);
			$table->string('password', 100)->nullable()->default(null);
			// These indices are required for efficient filtering for accessible and/or visible albums
			$table->index(['requires_link', 'is_public']); // for albums which don't require a direct link and are public
			$table->index(['is_public', 'password']); // for albums which are public and how no password
		});
	}

	private function populateBaseAlbumTable(): void
	{
		$publics = DB::table(self::TABLE_NAME)->whereNull('user_id')->get();
		foreach ($publics as $public) {
			DB::table('base_albums')
			->where('id', '=', $public->base_album_id)
			->update([
				self::IS_PUBLIC => true, // Duh !
				self::IS_LINK_REQUIRED => $public->is_link_required,
				self::PASSWORD => $public->password,
				self::GRANTS_FULL_PHOTO_ACCESS => $public->grants_full_photo_access,
				self::GRANTS_DOWNLOAD => $public->grants_download,
			]);
		}
	}
};
