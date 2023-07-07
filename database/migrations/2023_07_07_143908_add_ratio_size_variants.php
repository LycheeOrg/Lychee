<?php

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {

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
		Schema::table('size_variants', function (Blueprint $table) {
			// This index is required by \App\Actions\SizeVariant\Delete::do()
			// for `SizeVariant::query()`
			$table->float('ratio')->after('height')->default(1);
			$table->index(['photo_id','type','ratio']);
		});

		DB::table('size_variants')
			->where('height','>', 0)
			->update(['ratio' => DB::raw('width / height')]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{

	Schema::table('photos', function (Blueprint $table) {
		$this->dropIndexIfExists($table, 'photo_id_type_ratio_index');
	});

	Schema::table('size_variants', function (Blueprint $table) {
		$table->dropColumn('ratio');
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
