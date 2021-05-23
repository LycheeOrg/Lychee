<?php

use App\Models\PatchedBaseModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefactorTimestamps extends Migration
{
	private const SQL_DATETIME_FORMAT = 'Y-m-d H:i:s';
	private const PHOTOS_TABLE_NAME = 'photos';
	private const ID_COL_NAME = 'id';
	private const CREATED_AT_COL_NAME = 'created_at';
	private const UPDATED_AT_COL_NAME = 'updated_at';
	private const TAKEN_AT_COL_NAME = 'taken_at';
	private const TAKEN_AT_TZ_COL_NAME = 'taken_at_orig_tz';
	// The longest named timezones are "America/North_Dakota/New_Salem" and
	// "America/Argentina/Buenos_Aires" (both have 30 letters)
	private const TZ_NAME_MAX_LENGTH = 31;
	private const TAKESTAMP_COL_NAME = 'takestamp';
	private const DATETIME_PRECISION = 0;
	private const CONFIGS_TABLE_NAME = 'configs';
	private const CONFIG_KEY_COL_NAME = 'key';
	private const CONFIG_KEY = 'sorting_Photos_col';
	private const CONFIG_VALUE_COL_NAME = 'value';
	private const CONFIG_VALUE_OLD = 'takestamp';
	private const CONFIG_VALUE_NEW = 'taken_at';
	private const CONFIG_RANGE_COL_NAME = 'type_range';
	private const CONFIG_RANGE_OLD = 'id|takestamp|title|description|public|star|type';
	private const CONFIG_RANGE_NEW = 'id|taken_at|title|description|public|star|type';

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table(self::PHOTOS_TABLE_NAME, function (Blueprint $table) {
			$table->dateTime(
				self::CREATED_AT_COL_NAME,
				self::DATETIME_PRECISION
			)->nullable(false)->change();
			$table->dateTime(
				self::UPDATED_AT_COL_NAME,
				self::DATETIME_PRECISION
			)->nullable(false)->change();
			$table->dateTime(
				self::TAKEN_AT_COL_NAME,
				self::DATETIME_PRECISION
			)->nullable(true)->default(null)->comment('relative to UTC');
			$table->string(
				self::TAKEN_AT_TZ_COL_NAME,
				self::TZ_NAME_MAX_LENGTH
			)->nullable(true)->default(null)->comment('the timezone at which the photo has originally been taken');
		});

		DB::beginTransaction();
		$photos = DB::table(self::PHOTOS_TABLE_NAME)->select([
			self::ID_COL_NAME,
			self::CREATED_AT_COL_NAME,
			self::UPDATED_AT_COL_NAME,
			self::TAKESTAMP_COL_NAME,
		])->lazyById();

		foreach ($photos as $photo) {
			// This conversion assumes a simple heuristic.
			// We assume that the timezone which previously had been used by
			// the database connection had been the same as the
			// default timezone of the PHP application, before the timezone
			// for the database connection has explicitly been configured to
			// always use PatchedBaseModel::DB_TIMEZONE_NAME.
			// Note that this assumption is always true for SQLite backends,
			// because SQLite is not an independent server process, but runs
			// as part of the application.
			// However, PostgreSQL and MySQL connections might use their
			// own default timezone, if the client does not request a specific
			// timezone through the SQL command "SET TIMEZONE TO ...".
			// We simply assume (or hope) that the SQL server is configured
			// to use the same default timezone as the PHP application such
			// that this timezone has been used previously.
			// We fetch the timestamps as SQL datetime string (without
			// timezone information), interpret them according to the
			// default timezone of the application, convert them to
			// PatchedBaseModel::DB_TIMEZONE_NAME and write them back to the
			// DB.
			$created_at = $this->upgradeDatetime($photo->{self::CREATED_AT_COL_NAME});
			$updated_at = $this->upgradeDatetime($photo->{self::UPDATED_AT_COL_NAME});
			$taken_at = $this->upgradeDatetime($photo->{self::TAKESTAMP_COL_NAME});

			// We set the timezone in which the photo has originally been
			// taken to the default timezone of the PHP application.
			// This is probably not correct for many photos which have been
			// taken around the world.
			// However, this approach will show the same behaviour as before
			// and thus does not introduce a regression.
			// When the user wants correct timezones, then the user is free
			// to run `php artisan lychee:exif_lens` and update timezone
			// information from the photos.
			// We don't do that here in order to avoid a time consuming
			// migration for large datasets.
			$taken_at_orig_tz = ($taken_at === null) ? null : date_default_timezone_get();

			DB::table(self::PHOTOS_TABLE_NAME)->where(self::ID_COL_NAME, '=', $photo->id)->update([
				self::CREATED_AT_COL_NAME => $created_at,
				self::UPDATED_AT_COL_NAME => $updated_at,
				self::TAKEN_AT_COL_NAME => $taken_at,
				self::TAKEN_AT_TZ_COL_NAME => $taken_at_orig_tz,
			]);
		}

		// Update sorting criterion and range in the configuration table
		$sortingCriterion = $this->getConfiguredSortingCriterion();
		if ($sortingCriterion == self::CONFIG_VALUE_OLD) {
			$sortingCriterion = self::CONFIG_VALUE_NEW;
		}
		$this->setConfiguredSortingCriterion($sortingCriterion, self::CONFIG_RANGE_NEW);

		DB::commit();

		Schema::table(self::PHOTOS_TABLE_NAME, function (Blueprint $table) {
			$table->dropColumn([self::TAKESTAMP_COL_NAME]);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table(self::PHOTOS_TABLE_NAME, function (Blueprint $table) {
			$table->dateTime(
				self::TAKESTAMP_COL_NAME,
				self::DATETIME_PRECISION
			)->nullable(true)->default(null);
		});

		DB::beginTransaction();
		$photos = DB::table(self::PHOTOS_TABLE_NAME)->select([
			self::ID_COL_NAME,
			self::CREATED_AT_COL_NAME,
			self::UPDATED_AT_COL_NAME,
			self::TAKEN_AT_COL_NAME,
			self::TAKEN_AT_TZ_COL_NAME,
		])->lazyById();

		foreach ($photos as $photo) {
			$created_at = $this->downgradeDatetime($photo->{self::CREATED_AT_COL_NAME});
			$updated_at = $this->downgradeDatetime($photo->{self::UPDATED_AT_COL_NAME});
			$takestamp = $this->convertDatetime(
				$photo->{self::TAKEN_AT_COL_NAME},
				PatchedBaseModel::DB_TIMEZONE_NAME,
				$photo->{self::TAKEN_AT_TZ_COL_NAME}
			);

			DB::table(self::PHOTOS_TABLE_NAME)->where(self::ID_COL_NAME, '=', $photo->id)->update([
				self::CREATED_AT_COL_NAME => $created_at,
				self::UPDATED_AT_COL_NAME => $updated_at,
				self::TAKESTAMP_COL_NAME => $takestamp,
			]);
		}

		// Downgrade sorting criterion and range in the configuration table
		$sortingCriterion = $this->getConfiguredSortingCriterion();
		if ($sortingCriterion == self::CONFIG_VALUE_NEW) {
			$sortingCriterion = self::CONFIG_VALUE_OLD;
		}
		$this->setConfiguredSortingCriterion($sortingCriterion, self::CONFIG_RANGE_OLD);

		DB::commit();

		Schema::table(self::PHOTOS_TABLE_NAME, function (Blueprint $table) {
			$table->dropColumn([
				self::TAKEN_AT_COL_NAME,
				self::TAKEN_AT_TZ_COL_NAME,
			]);
		});
	}

	protected function upgradeDatetime(?string $sqlDatetime): ?string
	{
		return $this->convertDatetime(
			$sqlDatetime,
			date_default_timezone_get(),
			PatchedBaseModel::DB_TIMEZONE_NAME
		);
	}

	protected function downgradeDatetime(?string $sqlDatetime): ?string
	{
		return $this->convertDatetime(
			$sqlDatetime,
			PatchedBaseModel::DB_TIMEZONE_NAME,
			date_default_timezone_get()
		);
	}

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

	protected function getConfiguredSortingCriterion(): string
	{
		$config = DB::table(self::CONFIGS_TABLE_NAME)->select([
			self::CONFIG_VALUE_COL_NAME,
		])->where(
			self::CONFIG_KEY_COL_NAME,
			'=',
			self::CONFIG_KEY
		)->first();

		return $config->{self::CONFIG_VALUE_COL_NAME};
	}

	protected function setConfiguredSortingCriterion(string $criterion, string $range): void
	{
		DB::table(self::CONFIGS_TABLE_NAME)->where(
			self::CONFIG_KEY_COL_NAME,
			'=',
			self::CONFIG_KEY
		)->update([
			self::CONFIG_VALUE_COL_NAME => $criterion,
			self::CONFIG_RANGE_COL_NAME => $range,
		]);
	}
}
