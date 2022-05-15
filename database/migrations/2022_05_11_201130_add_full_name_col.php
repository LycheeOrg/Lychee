<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

class AddFullNameCol extends Migration
{
	private const USERS = 'users';
	private const NAME = 'fullname';

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
	 */
	public function down()
	{
		$dbc = 'database.connections.' . Config::get('database.default');
		$database = Config::get($dbc);
		if ($database['driver'] == 'sqlite') {
			$this->msgSection->writeln(sprintf('<comment>Warning:</comment> %s not removed as it breaks in SQLite. Please do it manually', self::NAME));
		} else {
			Schema::table(self::USERS, function (Blueprint $table) {
				$table->dropColumn(self::NAME);
			});
		}
	}
}
