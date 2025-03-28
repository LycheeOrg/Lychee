<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	public const COL_NOT_ON_DOCKER = 'not_on_docker';
	public const COL_ORDER = 'order';
	public const COL_IS_EXPERT = 'is_expert';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$this->upConfigCategories();
		$this->upHideForDockerInstall();
		$this->upOrdering();
		$this->upExpert();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		$this->downExpert();
		$this->downOrdering();
		$this->downHideForDockerInstall();
		$this->downConfigCategories();
	}

	private function upConfigCategories(): void
	{
		Schema::create('config_categories', function (Blueprint $table) {
			$table->increments('id');
			$table->string('cat');
			$table->string('name');
			$table->text('description');
			$table->unsignedTinyInteger('order')->default(255);

			$table->index('cat');
			$table->unique('name');
		});

		DB::table('config_categories')->insert([
			['cat' => 'config', 'name' => 'Basics', 'description' => '', 'order' => 0],
			['cat' => 'lychee SE', 'name' => 'Lychee SE', 'description' => 'Unlock the full capabilities of Lychee with the <a href="https://lycheeorg.dev/get-supporter-edition/" class="text-primary-500 font-bold underline">Supporter Edition</a>.
Get exclusive features and support the development of Lychee. Sponsor LycheeOrg on GitHub and get your License key <a href="https://github.com/sponsors/LycheeOrg" class="text-primary-500 font-bold underline">here</a>.', 'order' => 1],
			['cat' => 'Gallery', 'name' => 'Gallery', 'description' => '', 'order' => 2],
			['cat' => 'Mod Welcome', 'name' => 'Landing page', 'description' => 'This module enables a landing page. Instead of arriving directly to the gallery view, it adds a bit of grandiose to the website entrance. Provide the url of the picture to be displayed and set the title.', 'order' => 3],
			['cat' => 'Footer', 'name' => 'Footer', 'description' => '', 'order' => 4],
			['cat' => 'Smart Albums', 'name' => 'Smart Albums', 'description' => '', 'order' => 5],
			['cat' => 'Image Processing', 'name' => 'Image Processing', 'description' => '', 'order' => 6],

			['cat' => 'Mod Search', 'name' => 'Search', 'description' => '', 'order' => 10],
			['cat' => 'Mod Timeline', 'name' => 'Timeline', 'description' => '', 'order' => 11],
			['cat' => 'Mod Frame', 'name' => 'Frame', 'description' => '', 'order' => 12],
			['cat' => 'Mod Map', 'name' => 'Map/GPS', 'description' => '', 'order' => 13],
			['cat' => 'Mod RSS', 'name' => 'RSS', 'description' => '', 'order' => 14],
			['cat' => 'Mod NSFW', 'name' => 'Sensitive', 'description' => '', 'order' => 15],
			['cat' => 'Mod Back Button', 'name' => 'Back Home', 'description' => '', 'order' => 16],
			['cat' => 'Mod Cache', 'name' => 'Cache', 'description' => '', 'order' => 17],
			['cat' => 'Mod Pro', 'name' => 'Pro', 'description' => '', 'order' => 18],
			['cat' => 'Symbolic Link', 'name' => 'Symbolic Link', 'description' => '', 'order' => 19],
			['cat' => 'Mod Privacy', 'name' => 'Privacy Options', 'description' => '', 'order' => 20],

			['cat' => 'Users Management', 'name' => 'Users Management', 'description' => '', 'order' => 100],
			['cat' => 'Admin', 'name' => 'Admin', 'description' => '', 'order' => 200],
		]);
	}

	private function downConfigCategories(): void
	{
		Schema::dropIfExists('config_categories');
	}

	private function upHideForDockerInstall(): void
	{
		Schema::table('configs', function (Blueprint $table): void {
			$table->boolean(self::COL_NOT_ON_DOCKER)->default(false)->after('level')->comment('Defines that this setting is not used/displayed in docker installations');
		});

		DB::table('configs')->whereIn('key', ['allow_online_git_pull', 'apply_composer_update', 'force_migration_in_production'])->update([self::COL_NOT_ON_DOCKER => true]);
	}

	private function downHideForDockerInstall(): void
	{
		Schema::table('configs', function (Blueprint $table) {
			$table->dropColumn(self::COL_NOT_ON_DOCKER);
		});
	}

	private function upOrdering(): void
	{
		Schema::table('configs', function (Blueprint $table): void {
			$table->unsignedSmallInteger(self::COL_ORDER)->default(32767)->after(self::COL_NOT_ON_DOCKER);
		});

		DB::table('configs')->where('key', 'config_sort_albums_by')->update([self::COL_ORDER => 0]);
	}

	private function downOrdering(): void
	{
		Schema::table('configs', function (Blueprint $table) {
			$table->dropColumn(self::COL_ORDER);
		});
	}

	private function upExpert(): void
	{
		Schema::table('configs', function (Blueprint $table): void {
			$table->boolean(self::COL_IS_EXPERT)->default(false)->after(self::COL_NOT_ON_DOCKER)->comment('Defines that this setting is only visible in expert view');
		});
	}

	private function downExpert(): void
	{
		Schema::table('configs', function (Blueprint $table) {
			$table->dropColumn(self::COL_IS_EXPERT);
		});
	}
};
