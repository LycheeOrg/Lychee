<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\LazyCollection;

return new class() extends Migration {
	private const SQL_DATETIME_FORMAT = 'Y-m-d H:i:s';
	private const ID_COL_NAME = 'id';
	// The longest named timezones are "America/North_Dakota/New_Salem" and
	// "America/Argentina/Buenos_Aires" (both have 30 letters)
	// private const TZ_NAME_MAX_LENGTH = 31;
	// private const DATETIME_PRECISION = 0;
	// All constants related to the Photos relation
	private const PHOTOS_TABLE_NAME = 'photos';
	private const PHOTO_CREATED_AT_COL_NAME = 'created_at';
	private const PHOTO_UPDATED_AT_COL_NAME = 'updated_at';
	private const PHOTO_TAKEN_AT_COL_NAME = 'taken_at';
	private const PHOTO_TAKEN_AT_TZ_COL_NAME = 'taken_at_orig_tz';
	private const PHOTO_TAKESTAMP_COL_NAME = 'takestamp';
	// All constants related to the Configs relation
	private const CONFIGS_TABLE_NAME = 'configs';
	private const CONFIG_KEY_COL_NAME = 'key';
	private const CONFIG_VALUE_COL_NAME = 'value';
	private const CONFIG_RANGE_COL_NAME = 'type_range';
	// All constants related to the configuration of Photo sorting (PS)
	private const CONFIG_PS_KEY = 'sorting_Photos_col';
	// private const CONFIG_PS_VALUE_OLD2NEW = ['takestamp' => 'taken_at'];
	private const CONFIG_PS_VALUE_NEW2OLD = ['taken_at' => 'takestamp'];
	private const CONFIG_PS_RANGE_OLD = 'id|takestamp|title|description|public|star|type';
	// All constants related to the configuration of Album sorting (AS)
	private const CONFIG_AS_KEY = 'sorting_Albums_col';
	private const CONFIG_AS_VALUE_NEW2OLD = [
		'min_taken_at' => 'min_takestamp',
		'max_taken_at' => 'max_takestamp',
	];
	private const CONFIG_AS_RANGE_OLD = 'id|title|description|public|max_takestamp|min_takestamp|created_at';
	private const DB_TIMEZONE_NAME = 'UTC';

	/**
	 * Run the migration.
	 */
	public function up(): void
	{
		$version = DB::table('configs')->select(['value'])->where('key', '=', 'version')->first()?->value;
		if ($version !== '040303') {
			return;
		}

		Schema::table(self::PHOTOS_TABLE_NAME, function (Blueprint $table) {
			$table->timestamp(
				self::PHOTO_TAKESTAMP_COL_NAME
			)->nullable(true);
		});

		if (!App::runningUnitTests()) {
			DB::beginTransaction();
		}
		/** @var LazyCollection<int,object{id:int,created_at:string|null,taken_at:string|null,taken_at_orig_tz:string|null,updated_at:string|null}> */
		/** @phpstan-ignore varTag.type (false positive: https://github.com/phpstan/phpstan/issues/11805) */
		$photos = DB::table(self::PHOTOS_TABLE_NAME)->select([
			self::ID_COL_NAME,
			self::PHOTO_CREATED_AT_COL_NAME,
			self::PHOTO_UPDATED_AT_COL_NAME,
			self::PHOTO_TAKEN_AT_COL_NAME,
			self::PHOTO_TAKEN_AT_TZ_COL_NAME,
		])->lazyById();

		foreach ($photos as $photo) {
			$created_at = $this->downgradeDatetime($photo->{self::PHOTO_CREATED_AT_COL_NAME});
			$updated_at = $this->downgradeDatetime($photo->{self::PHOTO_UPDATED_AT_COL_NAME});
			$takestamp = $this->convertDatetime(
				$photo->{self::PHOTO_TAKEN_AT_COL_NAME},
				self::DB_TIMEZONE_NAME,
				$photo->{self::PHOTO_TAKEN_AT_TZ_COL_NAME}
			);

			DB::table(self::PHOTOS_TABLE_NAME)->where(self::ID_COL_NAME, '=', $photo->id)->update([
				self::PHOTO_CREATED_AT_COL_NAME => $created_at,
				self::PHOTO_UPDATED_AT_COL_NAME => $updated_at,
				self::PHOTO_TAKESTAMP_COL_NAME => $takestamp,
			]);
		}

		// Downgrade sorting criterion and range in the configuration table
		$this->convertConfiguration(
			self::CONFIG_PS_KEY,
			self::CONFIG_PS_VALUE_NEW2OLD,
			self::CONFIG_PS_RANGE_OLD
		);
		$this->convertConfiguration(
			self::CONFIG_AS_KEY,
			self::CONFIG_AS_VALUE_NEW2OLD,
			self::CONFIG_AS_RANGE_OLD
		);

		if (!App::runningUnitTests()) {
			DB::commit();
		}

		Schema::table(self::PHOTOS_TABLE_NAME, function (Blueprint $table) {
			$table->dropColumn([
				self::PHOTO_TAKEN_AT_COL_NAME,
				self::PHOTO_TAKEN_AT_TZ_COL_NAME,
			]);
		});

		DB::table('configs')->where('key', 'version')->update(['value' => '040302']);
	}

	/**
	 * Reverse the migration.
	 */
	public function down(): void
	{
	}

	/**
	 * Converts an SQL datetime string without timezone information from the
	 * application's default timezone to the DB timezone (UTC).
	 *
	 * This conversion assumes a simple heuristic.
	 * We assume that the timezone which previously had been used by
	 * the database connection had been the same as the default timezone of
	 * the PHP application, before the timezone for the database connection
	 * has explicitly been configured to always use
	 * {@link \App\Models\PatchedBaseModel::DB_TIMEZONE_NAME}.
	 * Note that this assumption is always true for SQLite backends, because
	 * SQLite is not an independent server process, but runs as part of the
	 * application.
	 * However, PostgreSQL and MySQL connections might use their own default
	 * timezone, if the client does not request a specific timezone through
	 * the SQL command "SET TIMEZONE TO ...".
	 * We simply assume (or hope) that the SQL server is configured to use the
	 * same default timezone as the PHP application such that this timezone
	 * has been used previously.
	 * We fetch the timestamps as SQL datetime string (without timezone
	 * information), interpret them according to the default timezone of the
	 * application, convert them to
	 * {@link \App\Models\PatchedBaseModel::DB_TIMEZONE_NAME}
	 * and write them back to the DB.
	 *
	 * @param string|null $sqlDatetime an SQL datetime string without timezone information
	 *
	 * @return string|null the converted SQL datetime string without timezone information
	 */
	protected function upgradeDatetime(?string $sqlDatetime): ?string
	{
		return $this->convertDatetime(
			$sqlDatetime,
			date_default_timezone_get(),
			self::DB_TIMEZONE_NAME
		);
	}

	/**
	 * Converts an SQL datetime string without timezone information from DB
	 * timezone (UTC) to the application's default timezone.
	 *
	 * See {@link upgradeDatetime} for more information.
	 *
	 * @param string|null $sqlDatetime an SQL datetime string without timezone information
	 *
	 * @return string|null the converted SQL datetime string without timezone information
	 */
	protected function downgradeDatetime(?string $sqlDatetime): ?string
	{
		return $this->convertDatetime(
			$sqlDatetime,
			self::DB_TIMEZONE_NAME,
			date_default_timezone_get()
		);
	}

	/**
	 * Converts an SQL datetime string without timezone information from
	 * $oldTz to $newTz.
	 *
	 * @param string|null $sqlDatetime an SQL datetime string without timezone information
	 * @param string|null $oldTz       the name of the old timezone
	 * @param string|null $newTz       the name of the new timezone
	 *
	 * @return string|null the converted SQL datetime string without timezone information
	 */
	protected function convertDatetime(?string $sqlDatetime, ?string $oldTz, ?string $newTz): ?string
	{
		if ($sqlDatetime === null) {
			return null;
		}
		$result = Carbon::createFromFormat(
			self::SQL_DATETIME_FORMAT,
			$sqlDatetime,
			$oldTz
		);
		$result->setTimezone($newTz);

		return $result->format(self::SQL_DATETIME_FORMAT);
	}

	/**
	 * Gets the value of the configuration option for $key.
	 *
	 * @param string $key the key (aka name) of the configuration option
	 *
	 * @return string the current value of the configuration option
	 */
	protected function getConfiguration(string $key): string
	{
		$config = DB::table(self::CONFIGS_TABLE_NAME)
			->select([self::CONFIG_VALUE_COL_NAME])
			->where(self::CONFIG_KEY_COL_NAME, '=', $key)
			->first();

		return $config->{self::CONFIG_VALUE_COL_NAME};
	}

	/**
	 * Sets the value and range of the configuration option for $key.
	 *
	 * @param string $key   the key (aka name) of the configuration option
	 * @param string $value the new value for the configuration option
	 * @param string $range the new range for the configuration option
	 */
	protected function setConfiguration(string $key, string $value, string $range): void
	{
		DB::table(self::CONFIGS_TABLE_NAME)
			->where(self::CONFIG_KEY_COL_NAME, '=', $key)
			->update([
				self::CONFIG_VALUE_COL_NAME => $value,
				self::CONFIG_RANGE_COL_NAME => $range,
			]);
	}

	/**
	 * Converts the configuration option for $key with respect to the given
	 * conversion map $map and sets a new range for the configuration option.
	 *
	 * If the current value of the configuration option is not included in
	 * $map, then the value is not altered.
	 *
	 * @param string               $key   the key (aka name) of the configuration option
	 * @param array<string,string> $map   a mapping from old-to-new configuration values
	 * @param string               $range the new range for the configuration option
	 */
	protected function convertConfiguration(string $key, array $map, string $range): void
	{
		$value = $this->getConfiguration($key);
		if (array_key_exists($value, $map)) {
			$value = $map[$value];
		}
		$this->setConfiguration($key, $value, $range);
	}
};
