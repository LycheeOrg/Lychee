<?php

declare(strict_types=1);

namespace App\Legacy;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Used in migrations prior 2024_04_09_121410.
 */
abstract class BaseConfigMigration extends Migration
{
	/**
	 * @return array<int,array{key:string,value:string,confidentiality:string,cat:string,type_range:string,description:string}>
	 */
	abstract public function getConfigs(): array;

	/**
	 * Run the migrations.
	 */
	final public function up(): void
	{
		DB::table('configs')->insert($this->getConfigs());
	}

	/**
	 * Reverse the migrations.
	 */
	final public function down(): void
	{
		$keys = collect($this->getConfigs())->map(fn ($v) => $v['key'])->all();
		DB::table('configs')->whereIn('key', $keys)->delete();
	}
}
