<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	private const PHOTOS_TABLE_NAME = 'photos';
	private const ID_COL_NAME = 'id';
	private const SMALL_COL_NAME = 'small';
	private const SMALL_WIDTH_COL_NAME = 'small_width';
	private const SMALL_HEIGHT_COL_NAME = 'small_height';
	private const SMALL2X_COL_NAME = 'small2x';
	private const SMALL2X_WIDTH_COL_NAME = 'small2x_width';
	private const SMALL2X_HEIGHT_COL_NAME = 'small2x_height';
	private const MEDIUM_COL_NAME = 'medium';
	private const MEDIUM_WIDTH_COL_NAME = 'medium_width';
	private const MEDIUM_HEIGHT_COL_NAME = 'medium_height';
	private const MEDIUM2X_COL_NAME = 'medium2x';
	private const MEDIUM2X_WIDTH_COL_NAME = 'medium2x_width';
	private const MEDIUM2X_HEIGHT_COL_NAME = 'medium2x_height';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table(self::PHOTOS_TABLE_NAME, function (Blueprint $table) {
			$table->integer(self::SMALL_WIDTH_COL_NAME)->unsigned()->nullable()->default(null);
			$table->integer(self::SMALL_HEIGHT_COL_NAME)->unsigned()->nullable()->default(null);
			$table->integer(self::SMALL2X_WIDTH_COL_NAME)->unsigned()->nullable()->default(null);
			$table->integer(self::SMALL2X_HEIGHT_COL_NAME)->unsigned()->nullable()->default(null);
			$table->integer(self::MEDIUM_WIDTH_COL_NAME)->unsigned()->nullable()->default(null);
			$table->integer(self::MEDIUM_HEIGHT_COL_NAME)->unsigned()->nullable()->default(null);
			$table->integer(self::MEDIUM2X_WIDTH_COL_NAME)->unsigned()->nullable()->default(null);
			$table->integer(self::MEDIUM2X_HEIGHT_COL_NAME)->unsigned()->nullable()->default(null);
		});

		DB::beginTransaction();
		$photos = DB::table(self::PHOTOS_TABLE_NAME)->select([
			self::ID_COL_NAME,
			self::SMALL_COL_NAME,
			self::SMALL2X_COL_NAME,
			self::MEDIUM_COL_NAME,
			self::MEDIUM2X_COL_NAME,
		])->lazyById();

		foreach ($photos as $photo) {
			$this->convertUp($photo->{self::SMALL_COL_NAME}, $smallWidth, $smallHeight); /** @phpstan-ignore-line */
			$this->convertUp($photo->{self::SMALL2X_COL_NAME}, $small2xWidth, $small2xHeight); /** @phpstan-ignore-line */
			$this->convertUp($photo->{self::MEDIUM_COL_NAME}, $mediumWidth, $mediumHeight); /** @phpstan-ignore-line */
			$this->convertUp($photo->{self::MEDIUM2X_COL_NAME}, $medium2xWidth, $medium2xHeight); /** @phpstan-ignore-line */
			DB::table(self::PHOTOS_TABLE_NAME)->where(self::ID_COL_NAME, '=', $photo->id)->update([
				self::SMALL_WIDTH_COL_NAME => $smallWidth,
				self::SMALL_HEIGHT_COL_NAME => $smallHeight,
				self::SMALL2X_WIDTH_COL_NAME => $small2xWidth,
				self::SMALL2X_HEIGHT_COL_NAME => $small2xHeight,
				self::MEDIUM_WIDTH_COL_NAME => $mediumWidth,
				self::MEDIUM_HEIGHT_COL_NAME => $mediumHeight,
				self::MEDIUM2X_WIDTH_COL_NAME => $medium2xWidth,
				self::MEDIUM2X_HEIGHT_COL_NAME => $medium2xHeight,
			]);
		}

		DB::commit();

		Schema::table(self::PHOTOS_TABLE_NAME, function (Blueprint $table) {
			$table->dropColumn([
				self::SMALL_COL_NAME,
				self::SMALL2X_COL_NAME,
				self::MEDIUM_COL_NAME,
				self::MEDIUM2X_COL_NAME,
			]);
		});
	}

	protected function convertUp(string $sizeString, ?int &$width, ?int &$height): void
	{
		$size = explode('x', $sizeString);
		$width = count($size) === 2 ? (int) ($size[0]) : null;
		$height = count($size) === 2 ? (int) ($size[1]) : null;
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table(self::PHOTOS_TABLE_NAME, function (Blueprint $table) {
			$table->string(self::SMALL_COL_NAME)->default('');
			$table->string(self::SMALL2X_COL_NAME)->default('');
			$table->string(self::MEDIUM_COL_NAME)->default('');
			$table->string(self::MEDIUM2X_COL_NAME)->default('');
		});

		DB::beginTransaction();
		$photos = DB::table(self::PHOTOS_TABLE_NAME)->select([
			self::ID_COL_NAME,
			self::SMALL_WIDTH_COL_NAME,
			self::SMALL_HEIGHT_COL_NAME,
			self::SMALL2X_WIDTH_COL_NAME,
			self::SMALL2X_HEIGHT_COL_NAME,
			self::MEDIUM_WIDTH_COL_NAME,
			self::MEDIUM_HEIGHT_COL_NAME,
			self::MEDIUM2X_WIDTH_COL_NAME,
			self::MEDIUM2X_HEIGHT_COL_NAME,
		])->lazyById();

		foreach ($photos as $photo) {
			/** @phpstan-ignore-next-line */
			$smallSize = $this->convertDown($photo->{self::SMALL_WIDTH_COL_NAME}, $photo->{self::SMALL_HEIGHT_COL_NAME});
			/** @phpstan-ignore-next-line */
			$small2xSize = $this->convertDown($photo->{self::SMALL2X_WIDTH_COL_NAME}, $photo->{self::SMALL2X_HEIGHT_COL_NAME});
			/** @phpstan-ignore-next-line */
			$mediumSize = $this->convertDown($photo->{self::MEDIUM_WIDTH_COL_NAME}, $photo->{self::MEDIUM_HEIGHT_COL_NAME});
			/** @phpstan-ignore-next-line */
			$medium2xSize = $this->convertDown($photo->{self::MEDIUM2X_WIDTH_COL_NAME}, $photo->{self::MEDIUM2X_HEIGHT_COL_NAME});

			DB::table(self::PHOTOS_TABLE_NAME)->where(self::ID_COL_NAME, '=', $photo->id)->update([
				self::SMALL_COL_NAME => $smallSize,
				self::SMALL2X_COL_NAME => $small2xSize,
				self::MEDIUM_COL_NAME => $mediumSize,
				self::MEDIUM2X_COL_NAME => $medium2xSize,
			]);
		}

		DB::commit();

		Schema::table(self::PHOTOS_TABLE_NAME, function (Blueprint $table) {
			$table->dropColumn([
				self::SMALL_WIDTH_COL_NAME,
				self::SMALL_HEIGHT_COL_NAME,
				self::SMALL2X_WIDTH_COL_NAME,
				self::SMALL2X_HEIGHT_COL_NAME,
				self::MEDIUM_WIDTH_COL_NAME,
				self::MEDIUM_HEIGHT_COL_NAME,
				self::MEDIUM2X_WIDTH_COL_NAME,
				self::MEDIUM2X_HEIGHT_COL_NAME,
			]);
		});
	}

	protected function convertDown(?int $width, ?int $height): string
	{
		return ($width !== null) ? $width . 'x' . $height : '';
	}
};
