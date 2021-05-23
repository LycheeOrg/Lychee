<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
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
			// always use 'UTC'.
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
			// default timezone of the application, convert them to UTC
			// and write them back to the DB.
			$created_at = Carbon::createFromFormat(
				self::SQL_DATETIME_FORMAT,
				$photo->{self::CREATED_AT_COL_NAME}
			);
			$created_at->setTimezone('UTC');
			$updated_at = Carbon::createFromFormat(
				self::SQL_DATETIME_FORMAT,
				$photo->{self::UPDATED_AT_COL_NAME}
			);
			$updated_at->setTimezone('UTC');
			$taken_at = Carbon::createFromFormat(
				self::SQL_DATETIME_FORMAT,
				$photo->{self::TAKESTAMP_COL_NAME}
			);
			$taken_at->setTimezone('UTC');

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
			DB::table(self::PHOTOS_TABLE_NAME)->where(self::ID_COL_NAME, '=', $photo->id)->update([
				self::CREATED_AT_COL_NAME => $created_at,
				self::UPDATED_AT_COL_NAME => $updated_at,
				self::TAKEN_AT_COL_NAME => $taken_at,
				self::TAKEN_AT_TZ_COL_NAME => Config::get('app.timezone'),
			]);
		}

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
			$created_at = Carbon::createFromFormat(
				self::SQL_DATETIME_FORMAT,
				$photo->{self::CREATED_AT_COL_NAME}, 'UTC'
			);
			$created_at->setTimezone(Config::get('app.timezone'));
			$updated_at = Carbon::createFromFormat(
				self::SQL_DATETIME_FORMAT,
				$photo->{self::UPDATED_AT_COL_NAME}, 'UTC'
			);
			$updated_at->setTimezone(Config::get('app.timezone'));
			$takestamp = Carbon::createFromFormat(
				self::SQL_DATETIME_FORMAT,
				$photo->{self::TAKEN_AT_COL_NAME}, 'UTC'
			);
			$takestamp->setTimezone($photo->{self::TAKEN_AT_TZ_COL_NAME});

			DB::table(self::PHOTOS_TABLE_NAME)->where(self::ID_COL_NAME, '=', $photo->id)->update([
				self::CREATED_AT_COL_NAME => $created_at,
				self::UPDATED_AT_COL_NAME => $updated_at,
				self::TAKESTAMP_COL_NAME => $takestamp,
			]);
		}

		DB::commit();

		Schema::table(self::PHOTOS_TABLE_NAME, function (Blueprint $table) {
			$table->dropColumn([
				self::TAKEN_AT_COL_NAME,
				self::TAKEN_AT_TZ_COL_NAME,
			]);
		});
	}
}
