<?php

namespace App;

use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
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
 *
 * @method static Builder|Logs newModelQuery()
 * @method static Builder|Logs newQuery()
 * @method static Builder|Logs query()
 * @method static Builder|Logs whereCreatedAt($value)
 * @method static Builder|Logs whereFunction($value)
 * @method static Builder|Logs whereId($value)
 * @method static Builder|Logs whereLine($value)
 * @method static Builder|Logs whereText($value)
 * @method static Builder|Logs whereType($value)
 * @method static Builder|Logs whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Logs extends Model
{
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
	 * Create a notice entry in the Log database.
	 *
	 * @param string $function
	 * @param string $line
	 * @param string $text
	 *
	 * @return bool returns true when successful
	 */
	public static function notice(string $function, string $line, string $text = '')
	{
		$log = self::create([
			'type' => 'notice',
			'function' => $function,
			'line' => $line,
			'text' => $text,
		]);
		try {
			$log->save();
		} catch (Exception $e) {
			return false;
		}

		return true;
	}

	/**
	 * Create a warning entry in the Log database.
	 *
	 * @param string $function
	 * @param string $line
	 * @param string $text
	 *
	 * @return bool returns true when successful
	 */
	public static function warning(string $function, string $line, string $text = '')
	{
		$log = self::create([
			'type' => 'warning',
			'function' => $function,
			'line' => $line,
			'text' => $text,
		]);

		return @$log->save();
	}

	/**
	 * create an error entry in the database.
	 *
	 * @param string $function
	 * @param string $line
	 * @param string $text
	 *
	 * @return bool returns true when successful
	 */
	public static function error(string $function, string $line, string $text = '')
	{
		$log = self::create([
			'type' => 'error',
			'function' => $function,
			'line' => $line,
			'text' => $text,
		]);

		return @$log->save();
	}
}
