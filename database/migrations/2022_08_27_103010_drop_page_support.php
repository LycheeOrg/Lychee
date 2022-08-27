<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DropPageSupport extends Migration
{
	private const SQL_TIMEZONE_NAME = 'UTC';
	private const SQL_DATETIME_FORMAT = 'Y-m-d H:i:s';
	private const ID_COL_NAME = 'id';
	private const CREATED_AT_COL_NAME = 'created_at';
	private const UPDATED_AT_COL_NAME = 'updated_at';
	private const DATETIME_PRECISION = 0;

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void
	{
		Schema::dropIfExists('page_contents');
		Schema::dropIfExists('pages');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void
	{
		$now = Carbon::now();
		if (!$this->needsConversion()) {
			$now->setTimezone('UTC');
		}
		$strNow = $now->format(self::SQL_DATETIME_FORMAT);

		Schema::dropIfExists('pages');
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

		DB::table('pages')->insert([
			[
				'title' => 'gallery',
				'menu_title' => 'gallery',
				'in_menu' => true,
				'link' => '/gallery',
				'enabled' => true,
				'order' => 2,
				'created_at' => $strNow,
				'updated_at' => $strNow, // also set `updated_at` to ensure that `updated_at` is not before `created_at`
			],
		]);

		Schema::dropIfExists('page_contents');
		Schema::create('page_contents', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('page_id')->unsigned();
			$table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');
			$table->text('content');
			$table->string('class', 150);
			$table->enum('type', ['div', 'img']);
			$table->integer('order')->default(0);
			$table->timestamps();
		});

		$this->upgradeORMSystemTimesByTable('pages');
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
		DB::beginTransaction();
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
		DB::commit();
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
	 * Returns true, if a date/time value must be converted from the default
	 * timezone of the application from/to UTC during up-/downgrade.
	 *
	 * @return bool true, if conversion is required, false if not
	 */
	protected function needsConversion(): bool
	{
		$dbConnType = Config::get('database.default');
		switch ($dbConnType) {
			case 'mysql':
				return false;
			case 'sqlite':
			case 'pgsql':
				return true;
			default:
				// What is about sqlsrv? Is this actually used?
				throw new InvalidArgumentException('Unsupported DB system: ' . $dbConnType);
		}
	}
}
