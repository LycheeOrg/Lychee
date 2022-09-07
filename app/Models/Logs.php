<?php

namespace App\Models;

use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\UseFixedQueryBuilder;
use App\Models\Extensions\UTCBasedTimes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Logs.
 *
 * @property int         $id
 * @property string      $type
 * @property string      $function
 * @property int         $line
 * @property string      $text
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Logs extends Model
{
	use UTCBasedTimes;
	use ThrowsConsistentExceptions;
	/** @phpstan-use UseFixedQueryBuilder<Logs> */
	use UseFixedQueryBuilder;

	public const SEVERITY_EMERGENCY = 0;
	public const SEVERITY_ALERT = 1;
	public const SEVERITY_CRITICAL = 2;
	public const SEVERITY_ERROR = 3;
	public const SEVERITY_WARNING = 4;
	public const SEVERITY_NOTICE = 5;
	public const SEVERITY_INFO = 6;
	public const SEVERITY_DEBUG = 7;

	public const SEVERITY_2_STRING = [
		self::SEVERITY_EMERGENCY => 'emergency',
		self::SEVERITY_ALERT => 'alert',
		self::SEVERITY_CRITICAL => 'critical',
		self::SEVERITY_ERROR => 'error',
		self::SEVERITY_WARNING => 'warning',
		self::SEVERITY_NOTICE => 'notice',
		self::SEVERITY_INFO => 'info',
		self::SEVERITY_DEBUG => 'debug',
	];

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
		self::log(self::SEVERITY_NOTICE, $method, $line, $msg);
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
		self::log(self::SEVERITY_WARNING, $method, $line, $msg);
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
		self::log(self::SEVERITY_ERROR, $method, $line, $msg);
	}

	/**
	 * Writes a log entry.
	 *
	 * @param int    $severity the severity of the incident, must be one out
	 *                         of {@link Logs::SEVERITY_EMERGENCY},
	 *                         {@link Logs::SEVERITY_ALERT},
	 *                         {@link Logs::SEVERITY_CRITICAL},
	 *                         {@link Logs::SEVERITY_ERROR},
	 *                         {@link Logs::SEVERITY_WARNING},
	 *                         {@link Logs::SEVERITY_NOTICE},
	 *                         {@link Logs::SEVERITY_INFO} or
	 *                         {@link Logs::SEVERITY_DEBUG}
	 * @param string $method   the name of the method which triggers the log
	 *                         (use the magic constant `__METHOD__`, neither
	 *                         `__FUNCTION__` nor `__FILE__`)
	 * @param int    $line     the line which triggers the log
	 * @param string $msg      the message to log
	 *
	 * @phpstan-param int<0,7> $severity
	 */
	public static function log(int $severity, string $method, int $line, string $msg): void
	{
		try {
			if (strlen($method) > self::MAX_METHOD_LENGTH) {
				$method = '...' . substr($method, 3, self::MAX_METHOD_LENGTH - 3);
			}
			$log = new self([
				'type' => self::SEVERITY_2_STRING[$severity],
				'function' => $method,
				'line' => $line,
				'text' => $msg,
			]);
			$log->save();
		} catch (\Throwable) {
		}
	}
}
