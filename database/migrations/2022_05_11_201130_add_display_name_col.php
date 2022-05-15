<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

class AddDisplayNameCol extends Migration
{
	private const USERS = 'users';
	private const NAME = 'display_name';

	private string $driverName;
	private ConsoleOutput $output;
	private ConsoleSectionOutput $msgSection;

	public function __construct()
	{
		$connection = Schema::connection(null)->getConnection();
		$this->driverName = $connection->getDriverName();
		$this->output = new ConsoleOutput();
		$this->msgSection = $this->output->section();
	}

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void
	{
		if (!Schema::hasColumn(self::USERS, self::NAME)) {
			Schema::table(self::USERS, function (Blueprint $table) {
				$table->string(self::NAME, 128)->after('password')->nullable();
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 *
	 * @throws RuntimeException
	 */
	public function down(): void
	{
		switch ($this->driverName) {
			case 'sqlite':
				$this->msgSection->writeln(sprintf('<comment>Warning:</comment> %s not removed as it breaks in SQLite. Please do it manually', self::NAME));
				break;
			case 'mysql':
			case 'pgsql':
				Schema::table(self::USERS, function (Blueprint $table) {
					$table->dropColumn(self::NAME);
				});
				break;
			default:
				throw new RuntimeException('Unsupported DBMS');
		}
	}
}
