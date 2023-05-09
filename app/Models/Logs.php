<?php

namespace App\Models;

use App\Enum\SeverityType;
use App\Models\Builders\LogsBuilder;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\UTCBasedTimes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Logs.
 *
 * @property int          $id
 * @property SeverityType $type
 * @property string       $function
 * @property int          $line
 * @property string       $text
 * @property Carbon|null  $created_at
 * @property Carbon|null  $updated_at
 *
 * @method static LogsBuilder|Logs addSelect($column)
 * @method static LogsBuilder|Logs join(string $table, string $first, string $operator = null, string $second = null, string $type = 'inner', string $where = false)
 * @method static LogsBuilder|Logs joinSub($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false)
 * @method static LogsBuilder|Logs leftJoin(string $table, string $first, string $operator = null, string $second = null)
 * @method static LogsBuilder|Logs newModelQuery()
 * @method static LogsBuilder|Logs newQuery()
 * @method static LogsBuilder|Logs orderBy($column, $direction = 'asc')
 * @method static LogsBuilder|Logs query()
 * @method static LogsBuilder|Logs select($columns = [])
 * @method static LogsBuilder|Logs whereCreatedAt($value)
 * @method static LogsBuilder|Logs whereFunction($value)
 * @method static LogsBuilder|Logs whereId($value)
 * @method static LogsBuilder|Logs whereIn(string $column, string $values, string $boolean = 'and', string $not = false)
 * @method static LogsBuilder|Logs whereLine($value)
 * @method static LogsBuilder|Logs whereNotIn(string $column, string $values, string $boolean = 'and')
 * @method static LogsBuilder|Logs whereText($value)
 * @method static LogsBuilder|Logs whereType($value)
 * @method static LogsBuilder|Logs whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Logs extends Model
{
	use UTCBasedTimes;
	use ThrowsConsistentExceptions;

	public const MAX_METHOD_LENGTH = 100;

	/**
	 * allow these properties to be mass assigned.
	 */
	protected $fillable = [
		'type',
		'function',
		'line',
		'text',
	];

	/**
	 * @var array<string,string>
	 */
	protected $casts = [
		'type' => SeverityType::class,
	];

	/**
	 * @param $query
	 *
	 * @return LogsBuilder
	 */
	public function newEloquentBuilder($query): LogsBuilder
	{
		return new LogsBuilder($query);
	}

	/**
	 * Logs a notification.
	 *
	 * @param string $method the name of the method which triggers the log
	 *                       (use the magic constant `__METHOD__`, neither
	 *                       `__FUNCTION__` nor `__FILE__`)
	 * @param int    $line   the line which triggers the log
	 * @param string $msg    the message to log
	 */
	public static function notice(string $method, int $line, string $msg): void
	{
		self::log(SeverityType::NOTICE, $method, $line, $msg);
	}

	/**
	 * Logs a warning.
	 *
	 * @param string $method the name of the method which triggers the log
	 *                       (use the magic constant `__METHOD__`, neither
	 *                       `__FUNCTION__` nor `__FILE__`)
	 * @param int    $line   the line which triggers the log
	 * @param string $msg    the message to log
	 */
	public static function warning(string $method, int $line, string $msg): void
	{
		self::log(SeverityType::WARNING, $method, $line, $msg);
	}

	/**
	 * Logs an error.
	 *
	 * @param string $method the name of the method which triggers the log
	 *                       (use the magic constant `__METHOD__`, neither
	 *                       `__FUNCTION__` nor `__FILE__`)
	 * @param int    $line   the line which triggers the log
	 * @param string $msg    the message to log
	 */
	public static function error(string $method, int $line, string $msg): void
	{
		self::log(SeverityType::ERROR, $method, $line, $msg);
	}

	/**
	 * Writes a log entry.
	 *
	 * @param SeverityType $severity the severity of the incident, must be one out
	 *                               of {@link SeverityType::EMERGENCY},
	 *                               {@link SeverityType::ALERT},
	 *                               {@link SeverityType::CRITICAL},
	 *                               {@link SeverityType::ERROR},
	 *                               {@link SeverityType::WARNING},
	 *                               {@link SeverityType::NOTICE},
	 *                               {@link SeverityType::INFO} or
	 *                               {@link SeverityType::DEBUG}
	 * @param string       $method   the name of the method which triggers the log
	 *                               (use the magic constant `__METHOD__`, neither
	 *                               `__FUNCTION__` nor `__FILE__`)
	 * @param int          $line     the line which triggers the log
	 * @param string       $msg      the message to log
	 */
	public static function log(SeverityType $severity, string $method, int $line, string $msg): void
	{
		try {
			if (strlen($method) > self::MAX_METHOD_LENGTH) {
				$method = '...' . substr($method, 3, self::MAX_METHOD_LENGTH - 3);
			}
			$log = new self([
				'type' => $severity,
				'function' => $method,
				'line' => $line,
				'text' => $msg,
			]);
			$log->save();
		} catch (\Throwable) {
		}
	}
}
