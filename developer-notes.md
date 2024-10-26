# Developer Notes

This guide contains some tricks and "do"s and "don't"s for new developer.
In particular, it highlights some pitfalls one can easily trap into.

# TL;DR for the Impatient

 1. If you create a new Eloquent model, use the trait
    `\App\Models\Extensions\UTCBasedTimes`.
 2. If you write a database migration and create a new table, do not use
    the convenient method `\Illuminate\Database\Schema\Blueprint#timestamps`
    in order to create the columns `created_at` and `updated_at`.
    Instead, create them manually like this
    
        $table->dateTime('created_at', 0)->nullable(false);
        $table->dateTime('updated_at', 0)->nullable(false);
    
 3. If you write a database migration and need a column which stores a
    date and time, only use `\Illuminate\Database\Schema\Blueprint#dateTime`.
 4. Do not use the methods `\Illuminate\Database\Schema\Blueprint#timestamp`
    nor `datetime_tz` to create a column.
    Depending on the database back-end they are mapped to SQL types which
    show different functional behaviour and thus are not back-end
    agnostic.
    Basically, `timestamp` and `datetime_tz` should be considered to be
    buggy.
    
# Information on Date/Time Attributes and How Date/Time Values are Handled by the Lychee Application

## Summary

All date/time values are stored at the DB back-end relative to UTC without
explicit timezone information.
For communication between the PHP application and the DB back-end all
date/time values are converted to SQL strings relative to UTC without
explicit timezone indication in the string.
For DBMS which support to set an explicit timezone for the connection
between the DBMS server and the DBMS client of the application
(e.g. MySQL and PostgreSQL), the timezone of the connection is set to "UTC".
This setting resides in `./config/database.php` and should not be changed.
The default timezone of the application as set in `./config/app.php` or
`./.env` resp. is independent of the DBMS timezone and may be configured.
At the level of the PHP application all date/time values are `Carbon` objects.
Conversion of the timezone of a `Carbon` instance and UTC happens at the
application layer during hydration/dehydration from/to the DBMS
(that's where `\App\Models\Extension\UTCBasedTimes` comes into play).
If a date/time value is hydrated from the DBMS (in UTC) and no better
target timezone for the value is known, then the instantiated `Carbon`
object uses the application's default timezone, and the represented time
is correctly converted from UTC to the application's default timezone such
that the represented instant in time is kept the same.

## Background Information on SQL Types for Date/Time Storage

### Definitions

In the following _"auto-conversion"_ means:

 - INSERT/UPDATE: When an SQL string is input to the DB, the DB interprets the string according to the timezone of the SQL connection (if no explicit TZ is indicated by the string) or interprets it according to the explicitly indicated timezone, converts it to UTC and stores it as UTC.
 - SELECT: The DB converts the stored UTC time to the timezone of the SQL connection and outputs a string which represents a time relative to the SQL connection.

_"No auto-conversion"_ means:

 - INSERT/UPDATE: When an SQL string is input to the DB, the DB interprets the string as a UTC-based time.
   Any explicitly indicated timezone in the string is silently ignored.
 - SELECT: The DB outputs a string which represents a UTC-based time.

_Caveat:_

Please note, that in neither case the original timezone of the input string is stored.
There is no SQL type which provides that feature.
The original timezone is only relevant for the auto-conversion during INSERT/UPDATE.
If we want to preserve the original timezone, then this information needs to be stored in an extra column (as for the `taken_at` attribute of the `Photo` model).
Moreover, without auto-conversion, the timezone (i.e. UTC) of the storage layer does actual not matter.
A time is simply output by the DB as it has been input to the DB.

### SQL Types

#### PostgreSQL

     Name                                | Size    | Low     | High      | Res. | Auto-Conversion?
    -------------------------------------+---------+---------+-----------+------+------------------
     TIMESTAMP [(p)] [WITHOUT TIME ZONE] | 8 bytes | 4713 BC | 294276 AD | 1 µs | No
     TIMESTAMP [(p)] WITH TIME ZONE      | 8 bytes | 4713 BC | 294276 AD | 1 µs | Yes

#### MySQL/MariaDB

     Name      | Size    | Low        | High       | Res. | Auto-Conversion?
    -----------+---------+------------+------------+------+------------------
     DATETIME  | ?       | 1000-01-01 | 9999-12-31 | 1s   | No
     TIMESTAMP | 4 bytes | 1970-01-01 | 2038-01-19 | 1s   | Yes

#### SQLite

     Name      | Size    | Low        | High       | Res. | Auto-Conversion?
    -----------+---------+------------+------------+------+------------------
     DATETIME  | ?       | 0000-01-01 | 9999-12-31 | 1s   | No

### Comparison of SQL Types

Let us ignore the fact that PostgreSQL surpasses any other DB with respect to range and precision for each type, we have the following mapping between the types with respect to functional behaviour (i.e. auto-conversion vs. no auto-conversion):

     # | MySQL     | PostgreSQL                  | SQLite
    ---+-----------+-----------------------------+----------
     1 | DATETIME  | TIMESTAMP WITHOUT TIME ZONE | DATETIME
     2 | TIMESTAMP | TIMESTAMP WITH TIME ZONE    | n/a

### Conclusion

The Lychee application uses option 1, i.e. `DATETIME` for MySQL and `TIMESTAMP WITHOUT TIME ZONE` for PostgreSQL for the simple reason that MySQL provides the larger range for that type.
Otherwise, the application had to ensure that there are no date/time values before 1970 and after 2038 or MySQL will throw SQL exceptions during INSERT/UPDATE.
The lack of auto-conversion by the DB for the chosen option 1 is not a problem.
Correct conversion from/to UTC happens on the application layer.

### Eloquent Mappings

The class `\Illuminate\Database\Schema\Blueprint` provides several methods to create columns.
They map to the respective SQL types as follows

     Blueprint    | MySQL     | PostgreSQL                  | SQLite   | Remarks
    --------------+-----------+-----------------------------+----------+---------
     timestamps   | TIMESTAMP | TIMESTAMP WITHOUT TIME ZONE | DATETIME | Shortcut to create `updated_at` and `created_at`, uses `timestamp` under the hood
     timestamp    | TIMESTAMP | TIMESTAMP WITHOUT TIME ZONE | DATETIME | Broken!
     timestamp_tz | TIMESTAMP | TIMESTAMP WITH TIME ZONE    | DATETIME | Good!
     datetime     | DATETIME  | TIMESTAMP WITHOUT TIME ZONE | DATETIME | Good!
     datetime_tz  | DATETIME  | TIMESTAMP WITH TIME ZONE    | DATETIME | Broken!

With respect to functional behaviour, we have two "broken" mappings that should not be used.

 - The method `Blueprint::timestamp` maps to the MySQL type `TIMESTAMP` which performs auto-conversion, but the PostgreSQL type `TIMESTAMP WITHOUT TIME ZONE` does not.
 - The method `Blueprint::datetime_tz` maps to the MySQL type `DATETIME` which lacks auto-conversion, but the PostgreSQL type `TIMESTAMP WITH TIME ZONE` converts values.

This means only the methods `timestamp_tz` and `datetime` are usable in a DB-independent manner.
Also, the convenient method `timestamps` must not be used.
Taking into account the conclusion from above, the Lychee Application only uses `Blueprint::datetime`, because it shows identical behaviour for each DBMS and has no year-2038-problem on MySQL.


### Responses types

To generate proper responses types, we use Spatie Data + Spatie Typescript.

Create a new resource and add the attribute `#[TypeScript()]` from `use Spatie\TypeScriptTransformer\Attributes\TypeScript;`

Generate the types with:
```sh
php artisan typescript:transform
```

### Language translations

We use https://github.com/xiCO2k/laravel-vue-i18n
