<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Console\Commands;

use App\Models\Album;
use Illuminate\Console\Command;

class FixTree extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:fix-tree';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Fixes the nested set model of the tree';

	/**
	 * Execute the console command.
	 *
	 * @return int
	 *
	 * @throws \InvalidArgumentException
	 */
	public function handle(): int
	{
		$query = Album::query();

		$stat = $query->countErrors();

		$this->line('Tree statistics');
		$this->line('   Oddness:         ' . $stat['oddness']);
		$this->line('   Duplicates:      ' . $stat['duplicates']);
		$this->line('   Wrong parents:   ' . $stat['wrong_parent']);
		$this->line('   Missing parents: ' . $stat['missing_parent']);

		$total_errors = $stat['oddness'] + $stat['duplicates'] + $stat['wrong_parent'] + $stat['missing_parent'];
		if ($total_errors === 0) {
			$this->line('Everything OK, nothing to fix.');

			return 0;
		}

		$this->line('Found ' . $total_errors . ' errors.');
		$fixed_nodes = $query->fixTree();
		$this->line('Fixed ' . $fixed_nodes . ' nodes.');

		return 0;
	}
}
