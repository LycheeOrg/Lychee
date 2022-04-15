<?php

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class AddIndexForDelete.
 *
 * Adds an index which is required to efficiently find all paths of
 * size variants which can be safely deleted without breaking shared use of
 * the media file by a duplicate.
 */
class AddIndexForDelete extends Migration
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
		Schema::table('size_variants', function (Blueprint $table) {
			// This index is required by \App\Actions\Photo\Delete::do()
			// for `SizeVariant::query()`
			$table->unique(['short_path', 'photo_id']);
		});
	}

	public function down()
	{
		Schema::table('size_variants', function (Blueprint $table) {
			$this->dropUniqueIfExists($table, 'size_variants_short_path_photo_id_unique');
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
	private function dropUniqueIfExists(Blueprint $table, string $indexName)
	{
		$doctrineTable = $this->schemaManager->listTableDetails($table->getTable());
		if ($doctrineTable->hasIndex($indexName)) {
			$table->dropUnique($indexName);
		}
	}
}
