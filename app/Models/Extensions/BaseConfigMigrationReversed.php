<?php

namespace App\Models\Extensions;

use Illuminate\Support\Facades\DB;

abstract class BaseConfigMigrationReversed extends AbstractBaseConfigMigration
{
	/**
	 * Run the migrations.
	 */
	final public function up(): void
	{
		$keys = collect($this->getConfigs())->map(fn ($v) => $v['key'])->all();
		DB::table('configs')->whereIn('key', $keys)->delete();
	}

	/**
	 * Reverse the migrations.
	 */
	final public function down(): void
	{
		DB::table('configs')->insert($this->getConfigs());
	}
}
