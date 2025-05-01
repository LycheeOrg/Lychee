<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\LazyCollection;
use Safe\Exceptions\FilesystemException;
use function Safe\unlink;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

return new class() extends Migration {
	private ConsoleOutput $output;
	private ConsoleSectionOutput $msgSection;

	/**
	 * Outputs an error message.
	 *
	 * @param string $msg the message
	 */
	private function printError(string $msg): void
	{
		$this->msgSection->writeln('<error>Error:</error> ' . $msg);
	}

	/**
	 * Outputs a warning.
	 *
	 * @param string $msg the message
	 */
	private function printWarning(string $msg): void
	{
		$this->msgSection->writeln('<comment>Warning:</comment> ' . $msg);
	}

	/**
	 * Outputs an informational message.
	 *
	 * @param string $msg the message
	 */
	private function printInfo(string $msg): void
	{
		$this->msgSection->writeln('<info>Info:</info> ' . $msg);
	}

	public function __construct()
	{
		$this->output = new ConsoleOutput();
		$this->msgSection = $this->output->section();
	}

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::disableForeignKeyConstraints();
		$this->deleteAllSymLinks();
		Schema::dropIfExists('sym_links');
		Schema::enableForeignKeyConstraints();

		DB::table('configs')->whereIn('key', ['SL_enable', 'SL_life_time_days', 'SL_for_admin'])->delete();
		DB::table('config_categories')->where('cat', 'Symbolic Link')->delete();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::create('sym_links', function (Blueprint $table) {
			// Column definitions
			$table->bigIncrements('id')->nullable(false);
			$table->dateTime('created_at', 6)->nullable(false);
			$table->dateTime('updated_at', 6)->nullable(false);
			$table->unsignedBigInteger('size_variant_id')->nullable(false);
			$table->string('short_path')->nullable(false);
			// Indices and constraint definitions
			$table->index('created_at');
			$table->index('updated_at');
			$table->foreign('size_variant_id')->references('id')->on('size_variants');
			// This index is needed to efficiently find the latest symbolic link
			// for each size variant
			$table->index(['size_variant_id', 'created_at']);
		});

		DB::table('config_categories')->insert([
			['cat' => 'Symbolic Link', 'name' => 'Symbolic Link', 'description' => '', 'order' => 19],
		]);
		DB::table('configs')->insert([
			[
				'key' => 'SL_enable',
				'value' => '0',
				'cat' => 'Symbolic Link',
				'type_range' => '0|1',
				'description' => 'Enable symbolic link protection',
				'details' => '',
				'order' => 0,
				'is_secret' => false,
				'is_expert' => false,
			],
			[
				'key' => 'SL_for_admin',
				'value' => '0',
				'cat' => 'Symbolic Link',
				'type_range' => '0|1',
				'description' => 'Enable symbolic links on logged in admin user',
				'details' => '',
				'order' => 32767,
				'is_secret' => false,
				'is_expert' => true,
			],
			[
				'key' => 'SL_life_time_days',
				'value' => '7',
				'cat' => 'Symbolic Link',
				'type_range' => 'postive',
				'description' => 'Maximum life time for symbolic link',
				'details' => '',
				'order' => 1,
				'is_secret' => true,
				'is_expert' => false,
			],
		]);
	}

	private function deleteAllSymLinks(): void
	{
		/** @var LazyCollection<int,object{short_path:string}> */
		/** @phpstan-ignore varTag.type (false positive: https://github.com/phpstan/phpstan/issues/11805) */
		$entities = DB::table('sym_links')->select(['id', 'short_path'])->lazyById();
		foreach ($entities as $entity) {
			$path = $entity->short_path;
			$sym_absolute_path = public_path('sym') . '/' . $path;
			try {
				if (is_link($sym_absolute_path)) {
					$this->printInfo('Deleting symbolic link: ' . $sym_absolute_path);
					unlink($sym_absolute_path);
				}
			} catch (FilesystemException $e) {
				$this->printError('Failed to delete symbolic link: ' . $sym_absolute_path);
			}
		}
	}
};
