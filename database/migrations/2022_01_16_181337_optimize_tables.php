<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

return new class() extends Migration {
	private ConsoleOutput $output;
	private ConsoleSectionOutput $msgSection;

	public function __construct()
	{
		$this->output = new ConsoleOutput();
		$this->msgSection = $this->output->section();
	}

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$connection = Schema::connection(null)->getConnection();
		$tables = $connection->getDoctrineSchemaManager()->listTableNames();

		$driverName = $connection->getDriverName();

		match ($driverName) {
			'mysql' => $this->msgSection->writeln('<info>Info:</info> MySql/MariaDB detected.'),
			'pgsql' => $this->msgSection->writeln('<info>Info:</info> PostgreSQL detected.'),
			'sqlite' => $this->msgSection->writeln('<info>Info:</info> SQLite detected.'),
			default => $this->msgSection->writeln('<comment>Warning:</comment> Unknown DBMS; doing nothing.'),
		};

		$sql = match ($driverName) {
			'mysql' => 'ANALYZE TABLE ',
			'pgsql' => 'ANALYZE ',
			'sqlite' => 'ANALYZE ',
			default => 'NOTHING',
		};

		if ($sql === 'NOTHING') {
			return;
		}

		foreach ($tables as $table) {
			try {
				DB::statement($sql . $table);
				$this->msgSection->writeln('<info>Info:</info> ' . $table . ' optimized.');
			} catch (\Throwable $th) {
				$this->msgSection->writeln('<error>Error:</error> could not optimize ' . $table . '.');
				$this->msgSection->writeln('<error>Error:</error> ' . $th->getMessage());
			}
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// Nothing do to here.
	}
};
