<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public const CAT = 'Theme Colors';

	/**
	 * Run the migrations.
	 *
	 *  @codeCoverageIgnore Tested but before CI run...
	 */
	final public function up(): void
	{
		DB::table('config_categories')->where('cat', 'Gallery')->update(['description' => '<span class="text-warning-600 font-bold uppercase">Important:</span> Natural sorting and lexicographical sorting can produce different results.<br><span class="text-muted-color text-muted">Natural: img_1, img_2, img_10.<br>Lexicographical: img_1, img_10, img_2.<br>Lexicographical sorting is performed directly in the database, while natural sorting is performed in PHP after the database query runs. As a result, when natural sorting is combined with pagination, results are fetched from the database in an arbitrary order before being sorted, which can lead to unexpected results when browsing through pages. We recommend using natural sorting for a small number of photos, and lexicographical sorting for a large number of photos. Alternatively, you can prefix your numbers with 0s (e.g. img_01, img_02, img_10) so that both sorting methods produce the same result.</span>']);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @codeCoverageIgnore Tested but after CI run...
	 */
	final public function down(): void
	{
		DB::table('config_categories')->where('cat', 'Gallery')->update(['description' => '']);
	}
};
