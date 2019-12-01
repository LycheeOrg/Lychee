<?php

/** @noinspection PhpUndefinedClassInspection */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePagesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('pages')) {
			Schema::create('pages', function (Blueprint $table) {
				$table->increments('id');
				$table->string('title', 150)->default('');
				$table->string('menu_title', 100)->default('');
				$table->boolean('in_menu')->default(false);
				$table->boolean('enabled')->default(false);
				$table->string('link', 150)->default('');
				$table->integer('order')->default(0);
				$table->timestamps();
			});
		}

		if (!DB::table('pages')->where('title', 'gallery')->exists()) {
			DB::table('pages')->insert([
				//			[
				//				'title'      => 'contact',
				//				'menu_title' => 'contact',
				//				'in_menu'    => true,
				//				'link'       => '/contact',
				//				'enabled'    => true,
				//				'order'      => 0
				//			],
				//			[
				//				'title'      => 'about',
				//				'menu_title' => 'about',
				//				'in_menu'    => true,
				//				'link'       => '/about',
				//				'enabled'    => true,
				//				'order'      => 1
				//			],
				[
					'title' => 'gallery',
					'menu_title' => 'gallery',
					'in_menu' => true,
					'link' => '/gallery',
					'enabled' => true,
					'order' => 2,
				],
				//			[
				//				'title'      => 'portfolio',
				//				'menu_title' => 'portfolio',
				//				'in_menu'    => true,
				//				'link'       => '/portfolio',
				//				'enabled'    => true,
				//				'order'      => 3
				//			],
			]);
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (env('DB_DROP_CLEAR_TABLES_ON_ROLLBACK', false)) {
			Schema::dropIfExists('pages');
		}
	}
}
