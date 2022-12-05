<?php

namespace App\Models;

use App\Models\Extensions\SeverityType;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\UseFixedQueryBuilder;
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
 */
class Logs extends Model
{
	use UTCBasedTimes;
	use ThrowsConsistentExceptions;
	/** @phpstan-use UseFixedQueryBuilder<Logs> */
	use UseFixedQueryBuilder;

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
