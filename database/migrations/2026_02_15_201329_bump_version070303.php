<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

return new class() extends Migration {
	private ConsoleOutput $output;
	private ConsoleSectionOutput $msg_section;

	public function __construct()
	{
		$this->output = new ConsoleOutput();
		$this->msg_section = $this->output->section();
	}

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void
	{
		DB::table('configs')->where('key', 'version')->update(['value' => '070303']);
		try {
			Artisan::call('cache:clear');
		} catch (\Throwable $e) {
			$this->msg_section->writeln('<error>Warning:</error> Failed to clear cache for version 7.3.3');

			return;
		}
		$this->msg_section->writeln('<info>Info:</info> Cleared cache for version 7.3.3');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', 'version')->update(['value' => '070302']);
	}
};
