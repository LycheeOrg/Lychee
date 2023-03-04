<?php

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	private AbstractSchemaManager $schemaManager;
	private string $driverName;

	/**
	 * @throws DBALException
	 */
	public function __construct()
	{
		$connection = Schema::connection(null)->getConnection();
		$this->schemaManager = $connection->getDoctrineSchemaManager();
		$this->driverName = $connection->getDriverName();
	}

	public function up(): void
	{
		Schema::table('photos', function (Blueprint $table) {
			// These indices are needed to efficiently retrieve the covers of
			// albums acc. to different sorting criteria
			// Note, that covers are always sorted acc. to `is_starred` first.
			$table->index(['album_id', 'is_starred', 'title']);

			// In the case of mysql we apply the RAW query below.
			if ($this->driverName !== 'mysql') {
				$table->index(['album_id', 'is_starred', 'description']);
			}
		});

		// MySQL cannot create indices over unlimited string values
		// So we must explicitly define an upper bound on how many characters
		// are analyzed for sorting
		if ($this->driverName === 'mysql') {
			DB::statement('alter table `photos` add index `photos_album_id_is_starred_description(128)_index`(album_id, is_starred, description(128))');
		}
	}

	public function down(): void
	{
		$descriptionSQL = match ($this->driverName) {
			'mysql' => 'description(128)',
			default => 'description',
		};

		Schema::table('photos', function (Blueprint $table) use ($descriptionSQL) {
			$this->dropIndexIfExists($table, 'photos_album_id_is_starred_title_index');
			$this->dropIndexIfExists($table, 'photos_album_id_is_starred_' . $descriptionSQL . '_index');
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
