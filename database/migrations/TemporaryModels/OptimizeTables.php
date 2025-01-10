<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

class OptimizeTables
{
	private ConsoleOutput $output;
	private ConsoleSectionOutput $msgSection;
	private string $driverName;

	public function __construct()
	{
		$this->output = new ConsoleOutput();
		$this->msgSection = $this->output->section();
		$connection = Schema::connection(null)->getConnection();
		$this->driverName = $connection->getDriverName();
	}

	/**
	 * Run the optimization.
	 */
	public function exec(): void
	{
		/** @var array{name:string,schema:?string,size:int,comment:?string,collation:?string,engine:?string}[] */
		$tables = Schema::getTables();

		match ($this->driverName) {
			'mysql','pgsql','sqlite' => 'print nothing.',
			default => $this->msgSection->writeln('<comment>Warning:</comment> Unknown DBMS; doing nothing.'),
		};

		$sql = match ($this->driverName) {
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
				DB::statement($sql . $table['name']);
			} catch (\Throwable $th) {
				$this->msgSection->writeln('<error>Error:</error> could not optimize ' . $table['name'] . '.');
				$this->msgSection->writeln('<error>Error:</error> ' . $th->getMessage());
			}
		}
	}

	/**
	 * A helper function that allows to drop an index if exists.
	 *
	 * @param Blueprint       $table
	 * @param string|string[] $indexName
	 */
	public function dropIndexIfExists(Blueprint $table, string|array $indexName): void
	{
		$indexTableName = !is_array($indexName) ? $indexName : ($table->getTable() . '_' . implode('_', $indexName) . '_index');
		$indexes = collect(Schema::getIndexes($table->getTable()))->map(fn ($a) => $a['name'])->all();
		if (in_array($indexTableName, $indexes, true)) {
			$table->dropIndex($indexTableName);
		}
	}

	/**
	 * A helper function that allows to drop an unique constraint if exists.
	 *
	 * @param Blueprint $table
	 * @param string    $indexName
	 */
	public function dropUniqueIfExists(Blueprint $table, string $indexName): void
	{
		$indexes = collect(Schema::getIndexes($table->getTable()))->map(fn ($a) => $a['name'])->all();
		if (in_array($indexName, $indexes, true)) {
			$table->dropUnique($indexName);
		}
	}

	/**
	 * A helper function that allows to drop an foreign key if exists.
	 *
	 * @param Blueprint $table
	 * @param string    $indexName
	 */
	public function dropForeignIfExists(Blueprint $table, string $indexName): void
	{
		if ($this->driverName === 'sqlite') {
			return;
		}

		$fk = collect(Schema::getForeignKeys($table->getTable()))->map(fn ($a) => $a['name'])->all();
		if (in_array($indexName, $fk, true)) {
			$table->dropForeign($indexName);
		}
	}
}