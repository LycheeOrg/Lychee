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
		// Alpha user database is a bit broken on this one.
		// See https://github.com/LycheeOrg/Lychee/pull/3433#discussion_r2165674240
		DB::table('configs')->where('key', 'user_invitation_ttl')->update(['type_range' => 'positive']);

		DB::table('configs')->where('key', 'version')->update(['value' => '060612']);
		try {
			Artisan::call('cache:clear');
		} catch (\Throwable $e) {
			$this->msg_section->writeln('<error>Warning:</error> Failed to clear cache for version 6.6.12');

			return;
		}
		$this->msg_section->writeln('<info>Info:</info> Cleared cache for version 6.6.12');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', 'version')->update(['value' => '060611']);
	}
};
