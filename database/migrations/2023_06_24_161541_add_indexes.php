<?php

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

require_once 'TemporaryModels/OptimizeTables.php';

return new class() extends Migration {
	public const ACCESS_PERMISSIONS = 'access_permissions';
	private const CREATED_AT_COL_NAME = 'created_at';
	private const UPDATED_AT_COL_NAME = 'updated_at';
	private const DATETIME_PRECISION = 0;

	// Id names
	public const BASE_ALBUM_ID = 'base_album_id';
	public const USER_ID = 'user_id';

	// Attributes name
	public const IS_LINK_REQUIRED = 'is_link_required';
	// public const GRANTS_FULL_PHOTO_ACCESS = 'grants_full_photo_access';
	// public const GRANTS_DOWNLOAD = 'grants_download';
	// public const GRANTS_UPLOAD = 'grants_upload';
	// public const GRANTS_EDIT = 'grants_edit';
	// public const GRANTS_DELETE = 'grants_delete';
	public const PASSWORD = 'password';

	private AbstractSchemaManager $schemaManager;

	/**
	 * @throws DBALException
	 */
	public function __construct()
	{
		$connection = Schema::connection(null)->getConnection();
		$this->schemaManager = $connection->getDoctrineSchemaManager();
	}

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table(self::ACCESS_PERMISSIONS, function (Blueprint $table) {
			$table->index([self::IS_LINK_REQUIRED]); // for albums which don't require a direct link and are public
			$table->index([self::IS_LINK_REQUIRED, self::PASSWORD]); // for albums which are public and how no password
		});

		Schema::disableForeignKeyConstraints();
		Schema::table(self::ACCESS_PERMISSIONS, function ($table) {
			$table->dropColumn(self::CREATED_AT_COL_NAME);
		});
		Schema::table(self::ACCESS_PERMISSIONS, function ($table) {
			$table->dropColumn(self::UPDATED_AT_COL_NAME);
		});
		Schema::enableForeignKeyConstraints();

		$optimize = new OptimizeTables();
		$optimize->exec();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table(self::ACCESS_PERMISSIONS, function (Blueprint $table) {
			// Column definitions
			$table->dateTime(self::CREATED_AT_COL_NAME, self::DATETIME_PRECISION)->nullable();
			$table->dateTime(self::UPDATED_AT_COL_NAME, self::DATETIME_PRECISION)->nullable();
		});

		Schema::table(self::ACCESS_PERMISSIONS, function (Blueprint $table) {
			$this->dropIndexIfExists($table, self::ACCESS_PERMISSIONS . '_' . self::IS_LINK_REQUIRED . '_index');
			$this->dropIndexIfExists($table, self::ACCESS_PERMISSIONS . '_' . self::IS_LINK_REQUIRED . '_' . self::PASSWORD . '_index');
		});
	}

	/**
	 * A helper function that allows to drop an index if exists.
	 *
	 * @param Blueprint $table
	 * @param string    $indexName
	 *
	 * @throws DBALException
	 */
	private function dropIndexIfExists(Blueprint $table, string $indexName): void
	{
		$doctrineTable = $this->schemaManager->introspectTable($table->getTable());
		if ($doctrineTable->hasIndex($indexName)) {
			$table->dropIndex($indexName);
		}
	}
};
