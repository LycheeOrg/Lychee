<?php

use Illuminate\Support\Str;

return [
	/*
	|--------------------------------------------------------------------------
	| Default Database Connection Name
	|--------------------------------------------------------------------------
	|
	| Here you may specify which of the database connections below you wish
	| to use as your default connection for all database work. Of course
	| you may use many connections at once using the Database library.
	|
	*/

	'default' => env('DB_CONNECTION', 'mysql'),

	/*
	|--------------------------------------------------------------------------
	| Log DB SQL statements
	|--------------------------------------------------------------------------
	|
	| If set to true, all SQL statements will be logged to a text file below
	| storage.
	| Only use it for debugging and development purposes as it slows down
	| the performance of the application
	|
	*/

	'db_log_sql' => (bool) env('DB_LOG_SQL', false),
	'explain' => (bool) env('DB_LOG_SQL_EXPLAIN', false),

	/*
	|--------------------------------------------------------------------------
	| Database Connections
	|--------------------------------------------------------------------------
	|
	| Here are each of the database connections setup for your application.
	| Of course, examples of configuring each database platform that is
	| supported by Laravel is shown below to make development simple.
	|
	|
	| All database work in Laravel is done through the PHP PDO facilities
	| so make sure you have the driver for your particular database of
	| choice installed on your machine before you begin development.
	|
	*/

	'connections' => [
		'sqlite' => [
			'driver' => 'sqlite',
			'url' => env('DATABASE_URL'),
			'database' => env('DB_DATABASE', database_path('database.sqlite')),
			'prefix' => '',
			'foreign_key_constraints' => true,
		],

		'mysql' => [
			'driver' => 'mysql',
			'url' => env('DATABASE_URL'),
			'host' => env('DB_HOST', '127.0.0.1'),
			'port' => env('DB_PORT', '3306'),
			'database' => env('DB_DATABASE', 'forge'),
			'username' => env('DB_USERNAME', 'forge'),
			'password' => env('DB_PASSWORD', ''),
			'unix_socket' => env('DB_SOCKET', ''),
			'charset' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			// The timezone of the DB connection should always be set in order
			// to ensure that the PHP application and the DB server have a
			// mutual understanding how SQL datetime strings without an
			// explicit timezone indication such as `YYYY-MM-DD hh:mm:ss`
			// shall be interpreted.
			// The setting here must not be changed and must match the
			// assumption in \App\Models\PatchedBaseModel.
			'timezone' => '+00:00',
			'prefix' => '',
			'prefix_indexes' => true,
			'strict' => true,
			'engine' => 'InnoDB ROW_FORMAT=DYNAMIC',
			'options' => extension_loaded('pdo_mysql') ? array_filter([
				PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
			],
				fn ($elem) => ($elem !== null && $elem !== ''),
			) : [],
			// Ensure a deterministic SQL mode for MySQL/MariaDB.
			// Don't rely on accidentally correct, system-wide settings of the
			// DB service.
			'modes' => [
				// If strict mode is not enable, MySQL "cleverly" converts
				// invalid data on INSERT/UPDATE/etc. to something which MySQL
				// believes you wanted.
				// Overflow/underflow of values is silently ignored.
				// We want strict mode, because any error probably indicates
				// a bug in Lychee which should be fixed.
				'STRICT_ALL_TABLES',
				// same as above, but for transactional storage engines, like InnoDB
				'STRICT_TRANS_TABLES',
				// Nomen est omen, for some versions of MySQL not included in
				// `STRICT_ALL_TABLES` and hence must be set separately.
				'ERROR_FOR_DIVISION_BY_ZERO',
				// don't accept 00.00.0000 as a date
				'NO_ZERO_DATE',
				// don't accept dates as valid whose month or day component is
				// zero, i.e. refuse 00.05.2021 or 13.00.2021 as invalid
				'NO_ZERO_IN_DATE',
				// Disable the probably most stupid feature of MySQL.
				// If one INSERTS a DB row with id=0, then MySQL replaces the
				// ID with latest auto-increment value plus one. WTF?!
				// As our admin user has ID=0, we want 0 to be 0 when we
				// insert 0 and not some "auto-magical" replacement.
				'NO_AUTO_VALUE_ON_ZERO',
				// Don't silently use another DB engine, if the selected
				// DB engin (InnoDB) is not available.
				'NO_ENGINE_SUBSTITUTION ',
			],
		],

		'pgsql' => [
			'driver' => 'pgsql',
			'url' => env('DATABASE_URL'),
			'host' => env('DB_HOST', '127.0.0.1'),
			'port' => env('DB_PORT', '5432'),
			'database' => env('DB_DATABASE', 'forge'),
			'username' => env('DB_USERNAME', 'forge'),
			'password' => env('DB_PASSWORD', ''),
			'charset' => 'utf8',
			// The timezone of the DB connection should always be set in order
			// to ensure that the PHP application and the DB server have a
			// mutual understanding how SQL datetime strings without an
			// explicit timezone indication such as `YYYY-MM-DD hh:mm:ss`
			// shall be interpreted.
			// The setting here must not be changed and must match the
			// assumption in \App\Models\PatchedBaseModel.
			'timezone' => 'UTC',
			'prefix' => '',
			'prefix_indexes' => true,
			'search_path' => 'public',
			'sslmode' => 'prefer',
		],

		'sqlsrv' => [
			'driver' => 'sqlsrv',
			'url' => env('DATABASE_URL'),
			'host' => env('DB_HOST', 'localhost'),
			'port' => env('DB_PORT', '1433'),
			'database' => env('DB_DATABASE', 'forge'),
			'username' => env('DB_USERNAME', 'forge'),
			'password' => env('DB_PASSWORD', ''),
			'charset' => 'utf8',
			'prefix' => '',
			'prefix_indexes' => true,
		],
	],

	/*
	|--------------------------------------------------------------------------
	| Migration Repository Table
	|--------------------------------------------------------------------------
	|
	| This table keeps track of all the migrations that have already run for
	| your application. Using this information, we can determine which of
	| the migrations on disk haven't actually been run in the database.
	|
	*/

	'migrations' => 'migrations',

	/*
	|--------------------------------------------------------------------------
	| Redis Databases
	|--------------------------------------------------------------------------
	|
	| Redis is an open source, fast, and advanced key-value store that also
	| provides a richer body of commands than a typical key-value system
	| such as APC or Memcached. Laravel makes it easy to dig right in.
	|
	*/

	'redis' => [
		'client' => 'phpredis',

		'options' => [
			'cluster' => env('REDIS_CLUSTER', 'redis'),
			'prefix' => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'Lychee'), '_') . '_database_'),
		],

		'default' => [
			'scheme' => env('REDIS_SCHEME', 'tcp'),
			'path' => env('REDIS_PATH', null),
			'url' => env('REDIS_URL'),
			'host' => env('REDIS_HOST', '127.0.0.1'),
			'password' => env('REDIS_PASSWORD'),
			'port' => env('REDIS_PORT', '6379'),
			'database' => env('REDIS_DB', '0'),
		],

		'cache' => [
			'scheme' => env('REDIS_SCHEME', 'tcp'),
			'path' => env('REDIS_PATH', null),
			'url' => env('REDIS_URL'),
			'host' => env('REDIS_HOST', '127.0.0.1'),
			'password' => env('REDIS_PASSWORD', null),
			'port' => env('REDIS_PORT', '6379'),
			'database' => env('REDIS_CACHE_DB', '1'),
		],
	],

	// Only list fk keys in debug mode.
	'list_foreign_keys' => (bool) env('DB_LIST_FOREIGN_KEYS', false) && (bool) env('APP_DEBUG', false),
];
