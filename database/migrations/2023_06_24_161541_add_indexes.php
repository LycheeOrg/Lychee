<?php

declare(strict_types=1);

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

require_once 'TemporaryModels/OptimizeTables.php';

return new class() extends Migration {
	public const ACCESS_PERMISSIONS = 'access_permissions';

	// Id names
	public const BASE_ALBUM_ID = 'base_album_id';
	public const USER_ID = 'user_id';

	// Attributes name
	public const IS_LINK_REQUIRED = 'is_link_required';
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

		$optimize = new OptimizeTables();
		$optimize->exec();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
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
