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

	private const RANDOM_ID_LENGTH = 24;

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::dropIfExists('user_base_album');
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		$this->createUserBaseAlbumTable();
		DB::transaction(fn () => $this->populateUserBaseAlbumTable());

		$optimize = new OptimizeTables();
		$optimize->exec();
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
				insert([[
					self::USER_ID => $share->user_id,
					self::BASE_ALBUM_ID => $share->base_album_id,
				]]);
		}
	}
};
