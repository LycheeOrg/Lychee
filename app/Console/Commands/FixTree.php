<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Console\Commands;

use App\Models\Album;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

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
		// Normally we would not want that.
		// But here we need to disable strict mode to be able to fix the tree.
		Model::shouldBeStrict(false);
		$fixed_nodes = $query->fixTree();
		Model::shouldBeStrict(true);
		$this->line('Fixed ' . $fixed_nodes . ' nodes.');

		return 0;
	}
}
