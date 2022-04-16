<?php

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingIndices extends Migration
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
			// These indices are needed to efficiently retrieve the covers of
			// albums acc. to different sorting criteria
			// Note, that covers are always sorted acc. to `is_starred` first.
			$table->index(['album_id', 'is_starred', 'title']);
			$table->index(['album_id', 'is_starred', 'description']);
		});
	}

	public function down()
	{
		Schema::table('photos', function (Blueprint $table) {
			$this->dropIndexIfExists($table, 'photos_album_id_is_starred_title_index');
			$this->dropIndexIfExists($table, 'photos_album_id_is_starred_description_index');
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
	private function dropIndexIfExists(Blueprint $table, string $indexName)
	{
		$doctrineTable = $this->schemaManager->listTableDetails($table->getTable());
		if ($doctrineTable->hasIndex($indexName)) {
			$table->dropIndex($indexName);
		}
	}
}
