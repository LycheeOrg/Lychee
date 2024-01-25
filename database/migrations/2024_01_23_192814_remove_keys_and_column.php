<?php

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	private AbstractSchemaManager $schemaManager;

	public const TABLE = 'photos';
	public const COLUMN = 'is_public';

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
		Schema::table(self::TABLE, function (Blueprint $table) {
			$this->dropIndexIfExists($table, 'photos_album_id_is_public_index');
			$this->dropIndexIfExists($table, 'photos_album_id_is_starred_is_public_index');
		});
		Schema::disableForeignKeyConstraints();
		Schema::table(self::TABLE, function (Blueprint $table) {
			$table->dropColumn(self::COLUMN);
		});
		Schema::enableForeignKeyConstraints();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table(self::TABLE, function ($table) {
			$table->boolean(self::COLUMN)->nullable(false)->default(false);
		});
		Schema::table(self::TABLE, function (Blueprint $table) {
			$table->index(['album_id', self::COLUMN]);
			$table->index(['album_id', 'is_starred', self::COLUMN]);
		});
	}

	/**
	 * A helper function that allows to drop an index if exists.
	 *
	 * @param Blueprint $table
	 * @param string    $indexName
	 */
	private function dropIndexIfExists(Blueprint $table, string $indexName): void
	{
		$doctrineTable = $this->schemaManager->introspectTable($table->getTable());
		if ($doctrineTable->hasIndex($indexName)) {
			$table->dropIndex($indexName);
		}
	}
};
