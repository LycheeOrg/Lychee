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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\LazyCollection;

return new class() extends Migration {
	private const SQL_TIMEZONE_NAME = 'UTC';
	private const SQL_DATETIME_FORMAT = 'Y-m-d H:i:s';
	private const ID_COL_NAME = 'id';
	private const CREATED_AT_COL_NAME = 'created_at';
	private const UPDATED_AT_COL_NAME = 'updated_at';
	private const DATETIME_PRECISION = 0;
	private const TABLE_NAMES = [
		'albums',
		'logs',
		'pages',
		'photos',
		'sym_links',
		'users',
	];
	// The longest named timezones are "America/North_Dakota/New_Salem" and
	// "America/Argentina/Buenos_Aires" (both have 30 letters)
	private const TZ_NAME_MAX_LENGTH = 31;
	// All constants related to the Photos relation
	private const PHOTOS_TABLE_NAME = 'photos';
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
	private const CONFIG_PS_VALUE_OLD2NEW = ['takestamp' => 'taken_at'];
	private const CONFIG_PS_VALUE_NEW2OLD = ['taken_at' => 'takestamp'];
	private const CONFIG_PS_RANGE_OLD = 'id|takestamp|title|description|public|star|type';
	private const CONFIG_PS_RANGE_NEW = 'id|taken_at|title|description|public|star|type';
	// All constants related to the configuration of Album sorting (AS)
	private const CONFIG_AS_KEY = 'sorting_Albums_col';
	private const CONFIG_AS_VALUE_OLD2NEW = [
		'min_takestamp' => 'min_taken_at',
		'max_takestamp' => 'max_taken_at',
	];
	private const CONFIG_AS_VALUE_NEW2OLD = [
		'min_taken_at' => 'min_takestamp',
		'max_taken_at' => 'max_takestamp',
	];
	private const CONFIG_AS_RANGE_OLD = 'id|title|description|public|max_takestamp|min_takestamp|created_at';
	private const CONFIG_AS_RANGE_NEW = 'id|title|description|public|max_taken_at|min_taken_at|created_at';

	/**
	 * Run the migration.
	 */
	public function up(): void
	{
		try {
			$this->fixPagesTable();
			$this->upgradeORMSystemTimes();
			$this->upgradePhotos();
			$this->upgradeConfiguration();
		} catch (\Exception $e) {
			echo $e->getTraceAsString();
			throw $e;
		}
	}

	/**
	 * Reverse the migration.
	 */
	public function down(): void
	{
		try {
			$this->downgradeORMSystemTimes();
			$this->downgradePhotos();
			$this->downgradeConfiguration();
		} catch (\Exception $e) {
			echo $e->getTraceAsString();
			throw $e;
		}
	}

	/**
	 * Fixes a buggy row in the table Pages.
	 *
	 * This bug was introduced by the migration `CreatePagesTable` on
	 * 2019-02-21 11:43:56.
	 * Normally, the Eloquent ORM framework ensures that the columns
	 * `created_at` and `updated_at` are not null and are set correctly
	 * if one calls {@link \Illuminate\Database\Eloquent\Model::save()}.
	 * However, the migration manually inserted a row, by-passed the
	 * Eloquent ORM layer and did not set these columns.
	 */
	protected function fixPagesTable(): void
	{
		$now = Carbon::now();
		if (!$this->needsConversion()) {
			$now->setTimezone('UTC');
		}
		$strNow = $now->format(self::SQL_DATETIME_FORMAT);
		DB::table('pages')
			->whereNull('created_at')
			->update([
				'created_at' => $strNow,
				'updated_at' => $strNow, // also set `updated_at` to ensure that `updated_at` is not before `created_at`
			]);
		DB::table('pages')
			->whereNull('updated_at')
			->update(['updated_at' => $strNow]);
	}

	/**
	 * Upgrades the systems times `created_at` and `updated_at` for each table
	 * in {@link RefactorTimestampsAnew::TABLE_NAMES}.
	 */
	protected function upgradeORMSystemTimes(): void
	{
		foreach (self::TABLE_NAMES as $tableName) {
			$this->upgradeORMSystemTimesByTable($tableName);
		}
	}

	/**
	 * Upgrades the system times `created_at` and `updated_at` for the given
	 * table.
	 *
	 * @param string $tableName the name of the table to be upgraded
	 */
	protected function upgradeORMSystemTimesByTable(string $tableName): void
	{
		$nowString = Carbon::now(self::SQL_TIMEZONE_NAME)->format(self::SQL_DATETIME_FORMAT);

		// We must use three single calls to work around an SQLite limitation
		Schema::table($tableName, function (Blueprint $table) {
			$table->renameColumn(self::CREATED_AT_COL_NAME, self::CREATED_AT_COL_NAME . '_tmp');
		});
		Schema::table($tableName, function (Blueprint $table) {
			$table->renameColumn(self::UPDATED_AT_COL_NAME, self::UPDATED_AT_COL_NAME . '_tmp');
		});
		// Create the new columns as temporarily nullable
		Schema::table($tableName, function (Blueprint $table) {
			$table->dateTime(
				self::CREATED_AT_COL_NAME,
				self::DATETIME_PRECISION
			)->nullable();
			$table->dateTime(
				self::UPDATED_AT_COL_NAME,
				self::DATETIME_PRECISION
			)->nullable();
		});
		$needsConversion = $this->needsConversion();
		if (!App::runningUnitTests()) {
			DB::beginTransaction();
		}
		/** @var LazyCollection<int,object{id:int,created_at_tmp:string|null,updated_at_tmp:string|null}> */
		/** @phpstan-ignore varTag.type (false positive: https://github.com/phpstan/phpstan/issues/11805) */
		$entities = DB::table($tableName)->select([
			self::ID_COL_NAME,
			self::CREATED_AT_COL_NAME . '_tmp',
			self::UPDATED_AT_COL_NAME . '_tmp',
		])->lazyById();
		foreach ($entities as $entity) {
			$created_at = $entity->{self::CREATED_AT_COL_NAME . '_tmp'};
			$updated_at = $entity->{self::UPDATED_AT_COL_NAME . '_tmp'};
			if ($needsConversion) {
				$created_at = $this->upgradeDatetime($created_at) ?? $nowString;
				$updated_at = $this->upgradeDatetime($updated_at) ?? $nowString;
			}
			DB::table($tableName)->where(self::ID_COL_NAME, '=', $entity->id)->update([
				self::CREATED_AT_COL_NAME => $created_at,
				self::UPDATED_AT_COL_NAME => $updated_at,
			]);
		}
		DB::table($tableName)
			->whereNull(self::CREATED_AT_COL_NAME)
			->update([
				self::CREATED_AT_COL_NAME => $nowString,
				self::UPDATED_AT_COL_NAME => $nowString,
			]);
		DB::table($tableName)
			->whereNull(self::UPDATED_AT_COL_NAME)
			->update([self::UPDATED_AT_COL_NAME => $nowString]);
		if (!App::runningUnitTests()) {
			DB::commit();
		}
		// Make the new columns non-nullable
		Schema::table($tableName, function (Blueprint $table) {
			$table->dateTime(
				self::CREATED_AT_COL_NAME,
				self::DATETIME_PRECISION
			)->nullable(false)->change();
			$table->dateTime(
				self::UPDATED_AT_COL_NAME,
				self::DATETIME_PRECISION
			)->nullable(false)->change();
		});
		// We must use two single calls to work around an SQLite limitation
		Schema::table($tableName, function (Blueprint $table) {
			$table->dropColumn(self::CREATED_AT_COL_NAME . '_tmp');
		});
		Schema::table($tableName, function (Blueprint $table) {
			$table->dropColumn(self::UPDATED_AT_COL_NAME . '_tmp');
		});
	}

	/**
	 * Downgrades the systems times `created_at` and `updated_at` for each
	 * table in {@link RefactorTimestampsAnew::TABLE_NAMES}.
	 */
	protected function downgradeORMSystemTimes(): void
	{
		foreach (self::TABLE_NAMES as $tableName) {
			$this->downgradeORMSystemTimesByTable($tableName);
		}
	}

	/**
	 * Downgrades the system times `created_at` and `updated_at` for the given
	 * table.
	 *
	 * @param string $tableName the name of the table to be downgraded
	 */
	protected function downgradeORMSystemTimesByTable(string $tableName): void
	{
		// We must use three single calls to work around an SQLite limitation
		Schema::table($tableName, function (Blueprint $table) {
			$table->renameColumn(self::CREATED_AT_COL_NAME, self::CREATED_AT_COL_NAME . '_tmp');
		});
		Schema::table($tableName, function (Blueprint $table) {
			$table->renameColumn(self::UPDATED_AT_COL_NAME, self::UPDATED_AT_COL_NAME . '_tmp');
		});
		Schema::table($tableName, function (Blueprint $table) {
			$table->timestamps();
		});
		$needsConversion = $this->needsConversion();
		if (!App::runningUnitTests()) {
			DB::beginTransaction();
		}
		/** @var LazyCollection<int,object{id:int,created_at_tmp:string|null,updated_at_tmp:string|null}> */
		/** @phpstan-ignore varTag.type (false positive: https://github.com/phpstan/phpstan/issues/11805) */
		$entities = DB::table($tableName)->select([
			self::ID_COL_NAME,
			self::CREATED_AT_COL_NAME . '_tmp',
			self::UPDATED_AT_COL_NAME . '_tmp',
		])->lazyById();
		foreach ($entities as $entity) {
			$created_at = $entity->{self::CREATED_AT_COL_NAME . '_tmp'};
			$updated_at = $entity->{self::UPDATED_AT_COL_NAME . '_tmp'};
			if ($needsConversion) {
				$created_at = $this->downgradeDatetime($created_at);
				$updated_at = $this->downgradeDatetime($updated_at);
			}
			DB::table($tableName)->where(self::ID_COL_NAME, '=', $entity->id)->update([
				self::CREATED_AT_COL_NAME => $created_at,
				self::UPDATED_AT_COL_NAME => $updated_at,
			]);
		}
		if (!App::runningUnitTests()) {
			DB::commit();
		}
		// We must use two single calls to work around an SQLite limitation
		Schema::table($tableName, function (Blueprint $table) {
			$table->dropColumn(self::CREATED_AT_COL_NAME . '_tmp');
		});
		Schema::table($tableName, function (Blueprint $table) {
			$table->dropColumn(self::UPDATED_AT_COL_NAME . '_tmp');
		});
	}

	/**
	 * Upgrades the table Photos.
	 *
	 * It adds the columns `taken_at` and `taken_at_orig_tz`, sets the values
	 * of the newly added columns using the value of the column `takestamp`,
	 * converts the time value of required by the DBMS and drops the old
	 * column `takestamp` afterwards.
	 */
	protected function upgradePhotos(): void
	{
		Schema::table(self::PHOTOS_TABLE_NAME, function (Blueprint $table) {
			$table->dateTime(
				self::PHOTO_TAKEN_AT_COL_NAME,
				self::DATETIME_PRECISION
			)->nullable(true)->default(null)->comment('relative to UTC');
			$table->string(
				self::PHOTO_TAKEN_AT_TZ_COL_NAME,
				self::TZ_NAME_MAX_LENGTH
			)->nullable(true)->default(null)->comment('the timezone at which the photo has originally been taken');
		});
		$needsConversion = $this->needsConversion();
		if (!App::runningUnitTests()) {
			DB::beginTransaction();
		}
		/** @var LazyCollection<int,object{id:int,takestamp:string|null}> */
		/** @phpstan-ignore varTag.type (false positive: https://github.com/phpstan/phpstan/issues/11805) */
		$photos = DB::table(self::PHOTOS_TABLE_NAME)->select([
			self::ID_COL_NAME,
			self::PHOTO_TAKESTAMP_COL_NAME,
		])->lazyById();
		foreach ($photos as $photo) {
			$taken_at = $photo->{self::PHOTO_TAKESTAMP_COL_NAME};
			if ($needsConversion) {
				$taken_at = $this->upgradeDatetime($taken_at);
			}
			$taken_at_orig_tz = ($taken_at === null) ? null : date_default_timezone_get();

			DB::table(self::PHOTOS_TABLE_NAME)->where(self::ID_COL_NAME, '=', $photo->id)->update([
				self::PHOTO_TAKEN_AT_COL_NAME => $taken_at,
				self::PHOTO_TAKEN_AT_TZ_COL_NAME => $taken_at_orig_tz,
			]);
		}
		if (!App::runningUnitTests()) {
			DB::commit();
		}
		Schema::table(self::PHOTOS_TABLE_NAME, function (Blueprint $table) {
			$table->dropColumn([self::PHOTO_TAKESTAMP_COL_NAME]);
		});
	}

	/**
	 * Downgrades the table Photos.
	 *
	 * It adds the previous column `takestamp`, sets the value of the re-added
	 * column using the value of the column `taken_at`, converts the time
	 * value of required by the DBMS and drops the columns `taken_at`
	 * and `taken_at_orig_tz` afterwards.
	 */
	protected function downgradePhotos(): void
	{
		Schema::table(self::PHOTOS_TABLE_NAME, function (Blueprint $table) {
			$table->timestamp(
				self::PHOTO_TAKESTAMP_COL_NAME,
				self::DATETIME_PRECISION
			)->nullable(true)->default(null);
		});
		$needsConversion = $this->needsConversion();
		if (!App::runningUnitTests()) {
			DB::beginTransaction();
		}
		/** @var LazyCollection<int,object{id:int,taken_at:string|null,taken_at_orig_tz:string|null}> */
		/** @phpstan-ignore varTag.type (false positive: https://github.com/phpstan/phpstan/issues/11805) */
		$photos = DB::table(self::PHOTOS_TABLE_NAME)->select([
			self::ID_COL_NAME,
			self::PHOTO_TAKEN_AT_COL_NAME,
			self::PHOTO_TAKEN_AT_TZ_COL_NAME,
		])->lazyById();
		foreach ($photos as $photo) {
			$takestamp = $photo->{self::PHOTO_TAKEN_AT_COL_NAME};
			if ($needsConversion) {
				$takestamp = $this->convertDatetime(
					$takestamp,
					self::SQL_TIMEZONE_NAME,
					$photo->{self::PHOTO_TAKEN_AT_TZ_COL_NAME}
				);
			}
			DB::table(self::PHOTOS_TABLE_NAME)->where(self::ID_COL_NAME, '=', $photo->id)->update([
				self::PHOTO_TAKESTAMP_COL_NAME => $takestamp,
			]);
		}
		if (!App::runningUnitTests()) {
			DB::commit();
		}
		Schema::table(self::PHOTOS_TABLE_NAME, function (Blueprint $table) {
			$table->dropColumn([
				self::PHOTO_TAKEN_AT_COL_NAME,
				self::PHOTO_TAKEN_AT_TZ_COL_NAME,
			]);
		});
	}

	/**
	 * Upgrades sorting criterion and range in the configuration table.
	 */
	protected function upgradeConfiguration(): void
	{
		if (!App::runningUnitTests()) {
			DB::beginTransaction();
		}
		$this->convertConfiguration(
			self::CONFIG_PS_KEY,
			self::CONFIG_PS_VALUE_OLD2NEW,
			self::CONFIG_PS_RANGE_NEW
		);
		$this->convertConfiguration(
			self::CONFIG_AS_KEY,
			self::CONFIG_AS_VALUE_OLD2NEW,
			self::CONFIG_AS_RANGE_NEW
		);
		if (!App::runningUnitTests()) {
			DB::commit();
		}
	}

	/**
	 * Downgrades sorting criterion and range in the configuration table.
	 */
	protected function downgradeConfiguration(): void
	{
		if (!App::runningUnitTests()) {
			DB::beginTransaction();
		}
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
			self::SQL_TIMEZONE_NAME
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
			self::SQL_TIMEZONE_NAME,
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
			self::SQL_DATETIME_FORMAT . '+',
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

		return $config?->{self::CONFIG_VALUE_COL_NAME} ?? '';
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

	/**
	 * Returns true, if a date/time value must be converted from the default
	 * timezone of the application from/to UTC during up-/downgrade.
	 *
	 * @return bool true, if conversion is required, false if not
	 */
	protected function needsConversion(): bool
	{
		$dbConnType = Config::get('database.default');

		return match ($dbConnType) {
			'mysql' => false,
			'sqlite',
			'pgsql' => true,
			// What is about sqlsrv? Is this actually used?
			default => throw new InvalidArgumentException('Unsupported DB system: ' . $dbConnType),
		};
	}
};
