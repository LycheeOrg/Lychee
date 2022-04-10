<?php

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Removes some troublesome indices from DB.
 *
 * These troublesome indices mislead the MySQL query planner to pick the wrong
 * indices during query optimization.
 * Note, that we only drop single-column indices, but keep the multi-column
 * indices.
 * For example, we drop the index on `photos.taken_at` but keep the index
 * on `(photos.album_id, photos.taken_at)`.
 * Laravel never sorts _all_ photos of a gallery acc. to `taken_at`, it
 * only sorts photos of a specific album.
 * Hence, the multi-column indices are crucial and absolutely necessary
 * for efficient queries, but the single-column indices only disorient the
 * query planner.
 */
class DropObjectionableIndices extends Migration
{
	private AbstractSchemaManager $schemaManager;

	/**
	 * @throws DBALException
	 */
	public function __construct()
	{
		$connection = Schema::connection(null)->getConnection();
		$this->schemaManager = $connection->getDoctrineSchemaManager();
	}

	public function up()
	{
		Schema::table('photos', function (Blueprint $table) {
			$this->dropIndexIfExists($table, 'photos_created_at_index');
			$this->dropIndexIfExists($table, 'photos_updated_at_index');
			$this->dropIndexIfExists($table, 'photos_taken_at_index');
			$this->dropIndexIfExists($table, 'photos_is_public_index');
			$this->dropIndexIfExists($table, 'photos_is_starred_index');
		});

		Schema::table('sym_links', function (Blueprint $table) {
			$this->dropIndexIfExists($table, 'sym_links_updated_at_index');
		});
	}

	public function down()
	{
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
}